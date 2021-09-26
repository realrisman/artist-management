import {Component, OnInit, ViewChild} from '@angular/core';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {CelebrityService} from '../../services/celebrity.service';
import {Subscriber} from 'rxjs/Subscriber';
import {Observable} from 'rxjs/Observable';
import {MatDatepicker, MatSelect, MatSelectChange, MatSnackBar} from '@angular/material';
import {Celebrity} from '../../models/celebrity';
import {Link} from '../../models/link';
import * as moment from 'moment';
import {FormControl} from '@angular/forms';
import {Representative} from '../../models/representative';
import {isObject} from "util";
import {debounceTime, distinctUntilChanged, map, startWith, switchMap, concat} from 'rxjs/operators';
import {RepresentativesService} from '../../services/representatives.service';
import {CategoryService} from '../../services/category.service';

import 'tinymce';
import 'rxjs/add/operator/take';
import {AuthService} from "../../../common/services/auth.service";
import {MatDialog} from "@angular/material/dialog";
import {HttpErrorResponse, HttpParams} from "@angular/common/http";
import {MessageModalComponent} from "../message-modal/message-modal.component";
import {CompanyService} from "../../services/company.service";
import {Company} from "../../models/company";
import 'rxjs/add/observable/forkJoin';

declare var tinymce: any;

@Component({
    selector   : 'app-celebrity-edit',
    templateUrl: './celebrity-edit.component.html',
    styleUrls  : ['./celebrity-edit.component.css']
})
export class CelebrityEditComponent implements OnInit {

    @ViewChild(MatDatepicker) myDatepicker: MatDatepicker<Date>;
    @ViewChild('categoriesSelect') categoriesSelect: MatSelect;

    public isSaving: boolean    = true;
    public isChecking: boolean    = false;
    public checkEmailSuccess: boolean = false;
    public checkEmailUnknown: boolean = false;
    public checkEmailFail: boolean = false;
    public savedOk: boolean     = false;
    public celebrity: Celebrity = new Celebrity();

    public tinyMceConfig = {
        plugins                    : ['link', 'paste', 'table', 'code', 'image'],
        height                     : 300,
        file_browser_callback_types: 'image',
        images_upload_url          : '/data/upload',
        automatic_uploads          : true
    };

    representativeAutoComplete = new FormControl();
    representative: Representative;
    company: Company;
    representatives            = null;

    addtype: string      = "";
    addterritory: string = "";
    userRole:string = "";
    categories           = [];
    verifiedConnections = {};
    showLog = false;

    constructor(protected service: CelebrityService,
                protected representativesService: RepresentativesService,
                protected categoryService: CategoryService,
                protected companyService: CompanyService,
                private route: ActivatedRoute,
                private router: Router,
                protected snackBar: MatSnackBar,
                private auth: AuthService,
                public dialog: MatDialog) {
    }

    ngOnInit() {

        tinymce.baseURL = '/assets';
        tinymce.init(this.tinyMceConfig);

        this.route.paramMap.switchMap((params: ParamMap) => {
            console.log('params.get(\'id\')', params.get('id'));
            if ("add" == params.get('id')) {
                return new Observable<Celebrity>((subscriber: Subscriber<Celebrity>) => {
                        subscriber.next(new Celebrity());
                    }
                );
            } else {
                return this.service.details(params.get('id'));
            }
        }).subscribe((data) => {
            this.isSaving  = false;
            data['source'] = '';
            this.celebrity = <Celebrity>data;
            this.setStatusForTrainer();
        });

        this.categoryService.list('celebrities').subscribe((data) => {
            this.categories = <any>data;
        });
        this.representatives = this.representativeAutoComplete.valueChanges.pipe(
            startWith(null),
            debounceTime(200),
            distinctUntilChanged(),
            switchMap(val => {
                if (isObject(val)) {
                    console.log('got Rep object', val);
                    if('company' == val['type']){
                        this.company = <Company>val;
                        this.representative = null;
                    }else{
                        this.representative = <Representative>val;
                        this.company = null;
                    }
                    return Observable.empty();
                } else {
                    val = val || '';
                    let CompanyResult =  this.companyService.fetchCompaniesForAutocompleteOnCelebrityPage('name=' + encodeURIComponent(val))
                        .pipe(
                            map((response) => {
                                return response;
                            })
                        );
                    let RepResult = this.representativesService.fetchRepresentatives('name=' + encodeURIComponent(val))
                        .pipe(
                            map((response) => {
                                return response;
                            })
                        );
                    let emitter;
                    let observable = Observable.create(e => emitter = e);

                    Observable.forkJoin(CompanyResult, RepResult).subscribe((results)=>{
                        emitter.next([{
                            'name':'Representatives',
                            'options': results[1]
                        },{
                            'name':'Companies',
                            'options': results[0]
                        }]);
                        // emitter.next(results[0].concat(results[1]));
                    });

                    return observable;
                }
            })
        );
        this.auth.getRole().subscribe((role) => {
            if (role !='ROLE_WRITER' && role !='ROLE_IMAGE_UPLOADER' && role !='ROLE_SPECTATOR') {
                this.showLog = true;
            }
        })
    }

    protected loadCelebrityData() {
        this.service.details(this.celebrity.id).subscribe((data) => {
            this.isSaving  = false;
            this.celebrity = <Celebrity>data;
            this.setStatusForTrainer();
        });
    }

    protected setStatusForTrainer(){
        this.auth.getRole().subscribe((role) => {
            console.log('role',role,'status',this.celebrity.status);
            if('ROLE_TRAINER' == role && 'live' == this.celebrity.status){
                this.celebrity.status = 'ready';
            }
        })
    }

    displayFn(representative?: Representative|Company): string | undefined {
        return representative ? representative.name : undefined;
    }

    onSubmit($event) {
        console.log('submit event',$event);
    }

    addLink() {
        this.celebrity.links.push(new Link());
    }

    save(verify = false) {

        let data = {};
        Object.assign(data, this.celebrity);
        if (this.celebrity.birthdate) {
            data['birthdate'] = moment(this.celebrity.birthdate).format('YYYY-MM-DD');
        }
        data['verify'] = verify;
        console.log('saving', data, this.verifiedConnections);
        this.isSaving = true;
        this.savedOk  = false;
        this.service.save(data).subscribe((response) => {
            console.log('got response after save', response);
            this.isSaving = false;
            if (response['success']) {
                this.savedOk = true;
                this.snackBar.open('Saved successful!', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => this.onSaveSuccess());
            }  else if (response['blocked']) {
                this.savedOk = false;
                let dialogRef = this.dialog.open(MessageModalComponent, {
                    width: '400px',
                    data:{
                        message: {
                            type: 'Error',
                            text: response['error']
                        }
                    }
                });
            }else {
                let dialogRef = this.dialog.open(MessageModalComponent, {
                    width: '400px',
                    data:{
                        message: {
                            type: 'Error',
                            text: response['error']
                        }
                    }
                });
                this.isSaving = false;
                console.error(response['error']);
            }
        },(response) => {
            let message = {
                type: 'Error',
                text: 'Http error',
            };
            if(response instanceof HttpErrorResponse){
                message.text = response.message;
                message['details'] = response.error;
            }else{
                message.text = response.error;
                message['details'] = JSON.stringify(response);
            }
            let dialogRef = this.dialog.open(MessageModalComponent, {
                width: '400px',
                data:{
                    message: message
                }
            });
            this.isSaving = false;
            console.error(response);
        });
    }
    saveUploader() {
        let data = {};
        Object.assign(data, this.celebrity);
        console.log('saving', data);
        this.isSaving = true;
        this.savedOk  = false;
        this.service.saveUploader(data).subscribe((response) => {
            console.log('got response after save', response);
            this.isSaving = false;
            if (response['success']) {
                this.savedOk = true;
                this.snackBar.open('Saved successful!', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    this.router.navigate(["/celebrities"]);
                });
            } else {
                console.error(response['error']);
            }
        });
    }

    checkEmail(email){
        console.log(email);
        this.isChecking = true;

        let data = {};

        this.service.checkEmail({'email': email}).subscribe((response) => {
            console.log('got response after checkEmail', response);
            this.isChecking = false;

            if (response['success'] && response['status'] == 'unknown') {
                this.checkEmailSuccess = false;
                this.checkEmailFail = false;
                this.checkEmailUnknown = true;
            } else if(response['success'] && (response['status'] == 'webmail' || response['status'] == 'valid')){
                this.checkEmailSuccess = true;
                this.checkEmailFail = false;
                this.checkEmailUnknown = false;
            }
            else if (! response['success'] || response['status'] == 'invalid'){
                this.checkEmailSuccess = false;
                this.checkEmailFail = true;
                this.checkEmailUnknown = false;
            }
        },(response) => {
            let message = {
                type: 'Error',
                text: 'Http error',
            };
            if(response instanceof HttpErrorResponse){
                message.text = response.message;
                message['details'] = response.error;
            }else{
                message.text = response.error;
                message['details'] = JSON.stringify(response);
            }
            let dialogRef = this.dialog.open(MessageModalComponent, {
                width: '400px',
                data:{
                    message: message
                }
            });
            this.isSaving = false;
            console.error(response);
        });
    }

    removeLink(i) {
        this.celebrity.links.splice(i, 1);
    }

    removeRepresentative(i) {
        this.celebrity.representatives.splice(i, 1);
    }

    addRepresentative() {
        console.log('addRepresentative', this.representative, this.company, this.addtype);
        let length;
        if('' === this.addtype){
            this.dialog.open(MessageModalComponent, {
                width: '400px',
                data:{
                    message: {
                        type: 'Error',
                        text: 'Please select representative type'
                    }
                }
            });
            return;
        }
        let position = this.celebrity.representatives.filter(rep => rep.type==this.addtype).length+1;
        if(this.company !== null) {
            length        = this.celebrity.representatives.push({
                type          : this.addtype,
                is_company    : true,
                company       : this.company,
                territory     : this.addterritory,
                position      : position
            });
            this.companyService.details(this.company.id.toString(10)).take(1).subscribe((company) => {
                this.celebrity.representatives[length - 1].company = company;
            })
        }else{
            length        = this.celebrity.representatives.push({
                type          : this.addtype,
                is_company    : false,
                representative: this.representative,
                territory     : this.addterritory,
                position      : position
            });
            this.representativesService.details(this.representative.id.toString(10)).take(1).subscribe((representative) => {
                this.celebrity.representatives[length - 1].representative = representative;
            })
        }
        this.addtype      = '';
        this.addterritory = '';
        this.representativeAutoComplete.setValue('');
    }

    categoryCompareFn(c1, c2) {
        return c1 && c2 && (c1.id == c2.id);
    }

    public import() {
        this.isSaving = true;
        this.service.import(this.celebrity.id).subscribe((response) => {
            console.log('got response after import', response);
            this.isSaving = false;
            if (response['success'] && response['removed']) {
                this.snackBar.open('Celebrity deleted from WP', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    this.router.navigate(["/celebrities"]);
                });
                return;
            }
            if (response['success']) {
                this.loadCelebrityData();
            } else {
                console.error(response['error']);
            }
        });
    }

    protected onSaveSuccess(){
        this.route.queryParams.subscribe((params) => {
            if(params.hasOwnProperty('from') && 'verify' == params['from']){
                this.router.navigate(["/celebrities-need-verify"]);
            }else if(params.hasOwnProperty('from') && 'unable' == params['from']){
                this.router.navigate(["/celebrities-unable-verify"]);
            }else {
                this.router.navigate(["/celebrities"]);
            }
        });
    }
    public verify() {
        this.isSaving = true;
        this.service.verify(this.celebrity.id).subscribe((response) => {
            console.log('got response after verify', response);
            this.isSaving = false;
            if (response['success']) {
                this.snackBar.open('Celebrity data verified', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => this.onSaveSuccess());
                return;
            }else {
              console.error(response['error']);
            }
        });
    }

    fileChange($event) {
        if ($event.target.files.length > 0) {
            this.isSaving = true;
            this.service.featured(this.celebrity.id, $event.target.files[0]).subscribe((response) => {
                this.isSaving = false;
                console.log('response featured', response);
                if (response['success']) {
                    this.celebrity.image         = response['url'];
                    this.celebrity.attachment_id = response['attachment'];
                }
            });
        }
    }


    categoryChanged($event: MatSelectChange) {
        $event.value.forEach((cat, i) => {
            if (cat.parent) {
                //if parent is not selected
                if (!this.celebrity.categories.some((category) => {
                    return category.id == cat.parent;
                })) {
                    //find parent category and add it to selected
                    this.celebrity.categories = this.celebrity.categories.concat(this.categories.filter((category) => {
                        // console.log('checking cat with id', category.id);
                        return cat.parent == category.id
                    }));
                }
            }
        });
    }

    primaryClick(category, $event) {
        console.log('primary', category);
        this.celebrity.primaryCategory = category;
        this.categoriesSelect.close();

        //if category is already selected - do not propagate click to select element to prevent unselection of category
        //if category is not selected - let select process click and select category
        if (this.celebrity.categories.some((cat) => {
            return category.id == cat.id;
        })) {
            $event.stopPropagation();
        }
        $event.preventDefault();
    }
}
