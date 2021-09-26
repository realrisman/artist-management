import {Component, OnInit, ViewChild} from '@angular/core';
import {FormControl} from "@angular/forms";
import {CategoryService} from "../../services/category.service";
import {ActivatedRoute, ParamMap, Router} from "@angular/router";
import {MatDialog} from "@angular/material/dialog";
import {Observable} from "rxjs/Observable";
import {Subscriber} from "rxjs/Subscriber";
import {MessageModalComponent} from "../message-modal/message-modal.component";
import {HttpErrorResponse} from "@angular/common/http";
import { MatSelect } from '@angular/material';
import {MatSnackBar} from "@angular/material/snack-bar";
import {Company} from "../../models/company";
import {CompanyService} from "../../services/company.service";
import {MatSelectChange} from "@angular/material/select";

@Component({
  selector: 'app-company-edit',
  templateUrl: './company-edit.component.html',
  styleUrls: ['./company-edit.component.css']
})
export class CompanyEditComponent implements OnInit {

    @ViewChild('categoriesSelect') categoriesSelect: MatSelect;

    public isSaving: boolean = true;
    public savedOk: boolean = false;

    public isChecking = [];
    public checkEmailSuccess = [];
    public checkEmailUnknown = [];
    public checkEmailFail = [];

    company: Company = new Company();
    categories              = [];

    filtered: { companies } = {
        companies : null
    };

    companyAutoComplete   = new FormControl();

    constructor(protected companyService: CompanyService,
                protected categoryService: CategoryService,
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
                return new Observable<any>((subscriber: Subscriber<any>) => subscriber.next(new Company()));
            } else {
                return this.companyService.details(params.get('id'));
            }
        }).subscribe((data) => {
            this.isSaving = false;
            data['source'] = '';
            this.company   = <Company>data;
        });

        this.categoryService.list('representatives').subscribe((data) => {
            this.categories = <any>data;
        });
    }

    onSubmit($event) {

    }

    save(verify = false) {
        let data = {};
        Object.assign(data, this.company);
        data['verify'] = verify;
        console.log('saving', data);
        this.isSaving = true;
        this.savedOk = false;
        this.companyService.save(data).subscribe((response) => {
            console.log('got response after save', response);
            this.isSaving = false;
            if (response['success']) {
                this.savedOk = true;
                this.snackBar.open('Saved successful!', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    this.router.navigate(["/companies"]);
                });
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

    trackByFn(index: any, item: any) {
        return index;
    }

    categoryCompareFn(c1, c2) {
        return c1 && c2 && (c1.id == c2.id);
    }

    loadCompanyData() {
        this.isSaving = true;
        this.companyService.details(this.company.id.toString()).subscribe((data) => {
            this.isSaving = false;
            this.company  = <Company>data;
        })
    }

    public addLocation(){
        this.company.locations.push({ id:null, name:'', email:'', phone:'', postal_address:'', visitor_address:'' });
    }

    public removeLocation(i){
        this.company.locations.splice(i, 1);
    }
    public import() {
        this.isSaving = true;
        this.companyService.import(this.company.id).subscribe((response) => {
            console.log('got response after import', response);
            this.isSaving = false;
            if (response['success'] && response['removed']) {
                this.snackBar.open('Company deleted from WP', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    this.router.navigate(["/companies"]);
                });
                return;
            }
            if (response['success']) {
                this.loadCompanyData();
            } else {
                console.error(response['error']);
            }
        });
    }

    public verify() {
        this.isSaving = true;
        this.companyService.verify(this.company.id).subscribe((response) => {
            console.log('got response after verify', response);
            this.isSaving = false;
            if (response['success']) {
                this.snackBar.open('Representatives data verified', 'Ok', {
                    duration: 3000
                }).afterDismissed().subscribe(() => {
                    this.route.queryParams.subscribe((params) => {
                        if (params.hasOwnProperty('from') && 'verify' == params['from']) {
                            this.router.navigate(["/companies-need-verify"]);
                        } else {
                            this.router.navigate(["/companies"]);
                        }
                    });
                });
                return;
            } else {
                console.error(response['error']);
            }
        });
    }

    checkEmail(email, i){
        this.isChecking[i] = true;

        let data = {};

        this.companyService.checkEmail({'email': email}).subscribe((response) => {
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

    categoryChanged($event: MatSelectChange) {
        $event.value.forEach((cat, i) => {
            if (cat.parent) {
                //if parent is not selected
                if (!this.company.categories.some((category) => {
                    return category.id == cat.parent;
                })) {
                    //find parent category and add it to selected
                    this.company.categories = this.company.categories.concat(this.categories.filter((category) => {
                        // console.log('checking cat with id', category.id);
                        return cat.parent == category.id
                    }));
                }
            }
        });
    }

    primaryClick(category, $event) {
        console.log('primary', category, this.categoriesSelect);
        this.company.primaryCategory = category;
        this.categoriesSelect.close();

        //if category is already selected - do not propagate click to select element to prevent unselection of category
        //if category is not selected - let select process click and select category
        console.log('cats',this.company.categories);
        if (this.company.categories.some((cat) => {
            return category.id == cat.id;
        })) {
            $event.stopPropagation();
        }
        $event.preventDefault();
    }

    removeConnection(rc_id) {
        if (confirm('Delete?')) {
            this.companyService.removeConnection(rc_id).subscribe((response) => {
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

    fileChange($event) {
        if ($event.target.files.length > 0) {
            this.isSaving = true;
            this.companyService.featured(this.company.id, $event.target.files[0]).subscribe((response) => {
                this.isSaving = false;
                console.log('response featured', response);
                if (response['success']) {
                    this.company.image = response['url'];
                    this.company.attachment_id = response['attachment'];
                }
            });
        }
    }
}
