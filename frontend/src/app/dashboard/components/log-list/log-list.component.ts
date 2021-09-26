import {Component, OnInit, ViewChild} from '@angular/core';
import {UsersService} from '../../services/users.service';
import {LogService} from '../../services/log.service';
import {MatDatepicker, MatTabChangeEvent, PageEvent} from '@angular/material';
import {Subscription} from "rxjs";


@Component({
    selector   : 'app-log-list',
    templateUrl: './log-list.component.html',
    styleUrls  : ['./log-list.component.css']
})
export class LogListComponent implements OnInit {

    public loading: boolean = true;
    @ViewChild(MatDatepicker) myDatepicker: MatDatepicker<Date>;

    protected tabIndex                                                 = 0;
    public users: any[]                                                = [];
    public filter: { user, date,from, to, search, field, limit, offset, order } = {
        user  : "",
        date  : "",
        from  : "",
        to  : "",
        search: "",
        field : "name",
        limit : 10,
        offset: 0,
        order : ""
    };

    public total           = 0;
    public celebrities     = <any>[];
    public representatives = <any>[];
    public companies = <any>[];

    protected subscription: Subscription;

    constructor(protected userService: UsersService, protected logService: LogService) {
    }

    ngOnInit() {
        this.userService.short().subscribe((response) => {
            this.users = <any[]>response;
        });
        this.getData();
    }

    getData() {
        this.loading = true;
        if(this.subscription){
            this.subscription.unsubscribe();
        }
        if (this.tabIndex == 0) {
            this.subscription = this.logService.celebrities(this.filter).subscribe((response) => {
                this.celebrities = response['data'];
                this.total       = response['total'];
                this.loading     = false;
            });
        } else if(this.tabIndex == 1) {
            this.subscription = this.logService.representatives(this.filter).subscribe((response) => {
                this.representatives = response['data'];
                this.total           = response['total'];
                this.loading         = false;
            });
        } else {
            this.subscription = this.logService.companies(this.filter).subscribe((response) => {
                this.companies = response['data'];
                this.total           = response['total'];
                this.loading         = false;
            });
        }
    }

    onFilterChange() {
        console.log('filter change fired!');
        this.getData();
    }


    processPageEvent($event: PageEvent) {
        console.log('paging event', $event.pageIndex);
        this.filter.offset = this.filter.limit * $event.pageIndex;
        this.getData();
    }

    tabSwitch($event: MatTabChangeEvent) {
        console.log('tab switched', $event);
        this.tabIndex = $event.index;
        this.getData();
    }

    deleteEntity(id) {
        let observable;
        if (this.tabIndex == 0) {
            //delete celebrity entry
            observable = this.logService.deleteCelebrityLog(id);
        } else if(this.tabIndex == 1) {
            //delete representative entry
            observable = this.logService.deleteRepresentativeLog(id);
        }else {
            //delete representative entry
            observable = this.logService.deleteCompanyLog(id);
        }

        observable.subscribe((response) => {
            if (response.success) {
                this.getData();
            }
        });
    }
}
