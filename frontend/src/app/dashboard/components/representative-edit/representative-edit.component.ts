import {Component, OnInit, ViewChild} from '@angular/core';
import {Representative} from '../../models/representative';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {RepresentativesService} from '../../services/representatives.service';
import {Celebrity} from '../../models/celebrity';
import {Subscriber} from 'rxjs/Subscriber';
import {Observable} from 'rxjs/Observable';
import {CategoryService} from '../../services/category.service';
import {MatSelectChange, MatSnackBar, MatSelect} from '@angular/material';
import {FormControl} from "@angular/forms";
import {debounceTime, distinctUntilChanged, map, startWith, switchMap} from "rxjs/operators";
import {isObject} from "util";
import {HttpErrorResponse} from "@angular/common/http";
import {MessageModalComponent} from "../message-modal/message-modal.component";
import {MatDialog} from "@angular/material/dialog";
import {CompanyService} from "../../services/company.service";
import {CelebrityService} from "../../services/celebrity.service";
import {CelebrityFilter} from "../../models/celebrity-filter";

@Component({
    selector: 'app-representative-edit',
    templateUrl: './representative-edit.component.html',
    styleUrls: ['./representative-edit.component.css']
})
export class RepresentativeEditComponent implements OnInit {

    @ViewChild('categoriesSelect') categoriesSelect: MatSelect;

    public isSaving: boolean = true;
    public isLoadingLocations: boolean = false;
    public savedOk: boolean = false;
    public isChecking = [];
    public checkEmailSuccess = [];
    public checkEmailUnknown = [];
    public checkEmailFail = [];

    representative: Representative = new Representative();
    categories = [];
    locations = [];

    filtered: { companies } = {
        companies : null
    };
    companyName: string = '';
    companyAutoComplete   = new FormControl();

    addtype: string      = "";
    addterritory: string = "";

    celebrityAutoComplete = new FormControl();
    celebrity: Celebrity;
    celebrities = null;

    constructor(protected representativesService: RepresentativesService,
                protected categoryService: CategoryService,
                protected companyService: CompanyService,
                protected celebrityService: CelebrityService,
                private route: ActivatedRoute,
                private router: Router,
                protected snackBar: MatSnackBar,
                public dialog: MatDialog
    ) {
    }

    ngOnInit() {
        this.route.paramMap.switchMap((params: ParamMap) => {
            console.log('params.get(\'id\')', params.get('id'));
            if ("add" == params.get('id')) {
                return new Observable<any>((subscriber: Subscriber<any>) => subscriber.next(new Representative()));
            } else {
                return this.representativesService.details(params.get('id'));
            }
        }).subscribe((data) => {
            this.isSaving = false;
            data['source'] = '';
            this.representative = <Representative>data;
            this.buildLocationsList();
        });

        this.categoryService.list('representatives').subscribe((data) => {
            this.categories = <any>data;
        });

        this.filtered['companies']  = this.companyAutoComplete.valueChanges.pipe(
            startWith(null),
            debounceTime(200),
            distinctUntilChanged(),
            switchMap(val => {
                if (isObject(val) || val == '') {
                    console.log('got company object', val);
                    if (isObject(val)) {
                        console.log('val is object', Object.assign({},val));
                        this.companyService.details(val.id).subscribe((details)=> {
                            this.companyName = '';
                            //prevent from adding company second time
                            if(!this.representative.companies.some(function(company){
                                return company.id == details.id;
                            })) {
                                this.representative.companies.push(details);
                                this.buildLocationsList();
                            }
                        });
                    }
                    return Observable.empty();
                } else {
                    console.log('switchmap', val);
                    val = val || '';
                    this.companyName = val;
                    return this.representativesService.fetchCompanies('name=' + encodeURIComponent(val))
                        .pipe(
                            map((response) => {
                                return response.filter((option) => {
                                    return option.company.toLowerCase().indexOf(val.toLowerCase()) !== -1
                                }).map((company) => {
                                    return company;
                                })
                            })
                        )
                }
            })
        );

        this.celebrities = this.celebrityAutoComplete.valueChanges.pipe(
            startWith(null),
            debounceTime(200),
            distinctUntilChanged(),
            switchMap(val => {
                if (isObject(val)) {
                    console.log('got Rep object', val);
                    this.celebrity = <Celebrity>val;

                    return Observable.empty();
                } else {
                    val         = val || '';
                    let filter  = new CelebrityFilter();
                    filter.name = (val);
                    return this.celebrityService.fetchQuick(filter)
                        .pipe(
                            map((response) => {
                                return response['data'];
                            })
                        );
                }
            })
        );
    }

    buildLocationsList(){
        let list = [];
        this.representative.companies.map((company)=>{
           list = list.concat(...company.locations);
        });
        console.log('list after al',list);
        this.locations = list;
        //if assigned location is removed - clear selected value in drop-down
        if(!list.some((location)=>{
            return location.id == this.representative.location.id
        })){
            this.representative.location = '';
        }
    }
    companyDisplayFn(company?: any): string | undefined {
        return company ? company.name : undefined;
    }

    addEmail() {
        this.representative.emails.push('');
    }

    removeEmail(i) {
        this.representative.emails.splice(i, 1);
    }

    addPhone() {
        this.representative.phones.push('');
    }

    removePhone(i) {
        this.representative.phones.splice(i, 1);
    }

    onSubmit($event) {

    }

    checkCreateNewCompanyAndSave(verify = false){
        if(this.representative.companies.some((company)=>{
            return company.id == null;
        })){
            if(confirm('Create new company?')){
                this.save(verify);
            }
        }else{
            this.save(verify);
        }

    }
    onSaveSuccess(){
        this.route.queryParams.subscribe((params) => {
            console.log('route params', params);
            if (params.hasOwnProperty('from') && 'verify' === params['from']) {
                this.router.navigate(["/representatives-need-verify"]);
            }else if(params.hasOwnProperty('from') && 'unable' == params['from']){
                this.router.navigate(["/representatives-unable-verify"]);
            } else {
                this.router.navigate(["/representatives"]);
            }
        });
    }
    save(verify = false) {
        let data = {};
        Object.assign(data, this.representative);
        data['verify'] = verify;
        console.log('saving', data);
        this.isSaving = true;
        this.savedOk = false;
        this.representativesService.save(data).subscribe((response) => {
            console.log('got response after save', response);
            this.isSaving = false;
            if (response['success']) {
                this.savedOk = true;
                this.snackBar.open('Saved successful!', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => this.onSaveSuccess());
            } else if (response['blocked']) {
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
            } else {
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
                details: ''
            };
            if(response instanceof HttpErrorResponse){
                message.text = response.message;
                message.details = response.error;
            }else{
                message.text = response.error;
                message.details = JSON.stringify(response);
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

    removeConnection(rc_id) {
        if (confirm('Delete?')) {
            this.representativesService.removeConnection(rc_id).subscribe((response) => {
                console.log('got response after save', response);
                this.isSaving = false;
                if (response['success']) {
                    this.snackBar.open('Celebrity connection removed!', 'Ok', {
                        duration: 3000
                    }).afterDismissed().subscribe(() => {
                        window.location.reload();
                    });
                } else {
                    console.error(response['error']);
                }
            });
        }
    }

    verifyConnection(rc_id) {
        this.representativesService.verifyConnection(rc_id).subscribe((response) => {
            console.log('got response after save', response);
            this.isSaving = false;
            if (response['success']) {
                this.snackBar.open('Celebrity connection verified!', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    window.location.reload();
                });
            } else {
                console.error(response['error']);
            }
        });
    }

    checkEmail(email, i){
        this.isChecking[i] = true;

        let data = {};

        this.representativesService.checkEmail({'email': email}).subscribe((response) => {
            this.isChecking[i] = false;

            if (response['success'] && response['status'] == 'unknown') {
                this.checkEmailSuccess[i] = false;
                this.checkEmailFail[i] = false;
                this.checkEmailUnknown[i] = true;
            } else if(response['success'] && (response['status'] == 'webmail' || response['status'] == 'valid' || response['status'] == 'accept_all')){
                this.checkEmailSuccess[i] = true;
                this.checkEmailFail[i] = false;
                this.checkEmailUnknown[i] = false;
            }
            else if (! response['success'] || response['status'] == 'invalid'){
                this.checkEmailSuccess[i] = false;
                this.checkEmailFail[i] = true;
                this.checkEmailUnknown[i] = false;
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

    trackByFn(index: any, item: any) {
        return index;
    }


    categoryCompareFn(c1, c2) {
        return c1 && c2 && (c1.id == c2.id);
    }
    locationCompareFn(c1, c2) {
        if(c1==="" && c2===""){
            return true;
        }
        return c1 && c2 && (c1.id == c2.id);
    }

    loadRepresentativeData() {
        this.isSaving = true;
        this.representativesService.details(this.representative.id.toString()).subscribe((data) => {
            this.isSaving = false;
            this.representative = <Representative>data;
            this.buildLocationsList();
        })
    }

    public import() {
        this.isSaving = true;
        this.representativesService.import(this.representative.id).subscribe((response) => {
            console.log('got response after import', response);
            this.isSaving = false;
            if (response['success'] && response['removed']) {
                this.snackBar.open('Representative deleted from WP', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    this.router.navigate(["/representatives"]);
                });
                return;
            }
            if (response['success']) {
                this.loadRepresentativeData();
            } else {
                console.error(response['error']);
            }
        });
    }

    public verify() {
        this.isSaving = true;
        this.representativesService.verify(this.representative.id).subscribe((response) => {
            console.log('got response after verify', response);
            this.isSaving = false;
            if (response['success']) {
                this.snackBar.open('Representatives data verified', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => this.onSaveSuccess());
                return;
            } else {
                console.error(response['error']);
            }
        });
    }

    fileChange($event) {
        if ($event.target.files.length > 0) {
            this.isSaving = true;
            this.representativesService.featured(this.representative.id, $event.target.files[0]).subscribe((response) => {
                this.isSaving = false;
                console.log('response featured', response);
                if (response['success']) {
                    this.representative.image = response['url'];
                    this.representative.attachment_id = response['attachment'];
                }
            });
        }
    }

    categoryChanged($event: MatSelectChange) {
        $event.value.forEach((cat, i) => {
            if (cat.parent) {
                //if parent is not selected
                if (!this.representative.categories.some((category) => {
                    return category.id == cat.parent;
                })) {
                    //find parent category and add it to selected
                    this.representative.categories = this.representative.categories.concat(this.categories.filter((category) => {
                        // console.log('checking cat with id', category.id);
                        return cat.parent == category.id
                    }));
                }
            }
        });
    }

    locationChanged($event: MatSelectChange) {
        console.log('location changed',$event);
        if($event.value) {
            this.assignFormAddress($event.value);
        }
    }

    assignFormAddress(location){
        this.representative.mailing_address = location.postal_address;
        this.representative.visitor_address = location.visitor_address;
    }

    primaryClick(category, $event) {
        console.log('primary', category, this.categoriesSelect);
        this.representative.primaryCategory = category;
        this.categoriesSelect.close();

        //if category is already selected - do not propagate click to select element to prevent unselection of category
        //if category is not selected - let select process click and select category
        if (this.representative.categories.some((cat) => {
            return category.id == cat.id;
        })) {
            $event.stopPropagation();
        }
        $event.preventDefault();
    }

    removeCompanyFromFilter(removed: any) {
        console.log('removeCompanyFromFilter',removed);
        this.representative.companies = this.representative.companies.filter((company) => {
            return removed.id != company.id;
        });
        this.buildLocationsList();
    }
    companyAdded($event){
         $event.preventDefault();
         if(!this.representative.companies.some((company)=>{
             return company.id === null;
         })) {
             this.representative.companies.push({
                 name     : this.companyName,
                 locations: [],
                 id       : null
             });
             this.companyName = '';
             this.buildLocationsList();
         }
    }

    displayFn(celebrity?: Celebrity): string | undefined {
        return celebrity ? celebrity.name : undefined;
    }

    addCelebrity() {
        console.log('addCelebrity', this.representative, this.addtype);
        let length;
        if('' === this.addtype){
            this.dialog.open(MessageModalComponent, {
                width: '400px',
                data:{
                    message: {
                        type: 'Error',
                        text: 'Please select celebrity type'
                    }
                }
            });
            return;
        }

        this.representative.celebrities.push({
            celebrity   : this.celebrity.name,
            celebrity_id: this.celebrity.id,
            rc_id       : null,
            territory   : this.addterritory,
            verifyRank  : 0.00,
            type        : this.addtype,
            created     : (new Date()).toLocaleDateString("en-US"),
            verifiedDate: (new Date()).toLocaleDateString("en-US")
        });

        this.addtype      = '';
        this.addterritory = '';
        this.celebrityAutoComplete.setValue('');
    }
}
