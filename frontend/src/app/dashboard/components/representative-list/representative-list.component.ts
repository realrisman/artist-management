import {Component, OnInit} from '@angular/core';
import {FormControl} from '@angular/forms';
import {GridOptions} from 'ag-grid';
import {RepresentativesService} from '../../services/representatives.service';
import {Router} from '@angular/router';
import {debounceTime, distinctUntilChanged, map, startWith, switchMap} from 'rxjs/operators';
import {Representative} from '../../models/representative';
import {Observable} from 'rxjs/Observable';
import {isObject} from "util";
import {RepresentativeEditCellRendererComponent} from '../cell-renderers/representative-edit-cell-renderer/representative-edit-cell-renderer.component';
import {RepresentativeLogCellRendererComponent} from '../cell-renderers/representative-log-cell-renderer/representative-log-cell-renderer.component';
import {PageEvent} from '@angular/material';
import {RepresentativeDeleteCellRendererComponent} from '../cell-renderers/representative-delete-cell-renderer/representative-delete-cell-renderer.component';
import {RepresentativeViewCellRendererComponent} from '../cell-renderers/representative-view-cell-renderer/representative-view-cell-renderer.component';
import {HttpParams} from "@angular/common/http";
import {VerifyCellRendererComponent} from "../cell-renderers/verify-cell-renderer/verify-cell-renderer.component";
import {MatDialog} from "@angular/material/dialog";
import {ExportModalComponent} from "../export-modal/export-modal.component";

@Component({
    selector   : 'app-representative-list',
    templateUrl: './representative-list.component.html',
    styleUrls  : ['./representative-list.component.css']
})
export class RepresentativeListComponent implements OnInit {
    representatives  = <any>[];
    total            = 0;
    loading: boolean = false;

    companyAutoComplete = new FormControl();
    public gridOptions: GridOptions;

    companies: any = [];

    columnDefs = [
        {
            headerName: '#',
            field     : 'id',
            width     : 55
        },
        {
            headerName: 'Name',
            field     : 'name',
            width     : 150
        },
        {
            headerName: 'Company',
            field     : 'company.name',
            width     : 150
        },
        {
            headerName  : 'Address',
            field       : 'mailing_address',
            width       : 250,
            cellRenderer: (params) => {
                return '<b>Mailing</b>: ' + params.data.mailing_address + "<br><b>Visitor</b>: " + params.data.visitor_address
            },
        },
        // {
        //     headerName: 'Visitor address',
        //     field     : 'visitor_address',
        //     width     : 170
        // },
        {
            headerName  : 'Phones',
            field       : 'phones',
            cellRenderer: (params) => {
                return params.data.phones.join("<br>")
            },
            width       : 120
        },
        {
            headerName  : 'Emails',
            field       : 'emails',
            cellRenderer: (params) => {
                return params.data.emails.join("<br>")
            },
            width       : 160
        },
        {
            headerName           : '',
            cellRendererFramework: RepresentativeViewCellRendererComponent,
            width                : 80
        },
        {
            headerName           : '',
            cellRendererFramework: RepresentativeEditCellRendererComponent,
            width                : 80,
            from                 : "list"
        },
        {
            headerName           : '',
            cellRendererFramework: RepresentativeLogCellRendererComponent,
            width                : 80
        },
        {
            headerName           : '',
            cellRendererFramework: RepresentativeDeleteCellRendererComponent,
            width                : 80
        },
        {
            headerName           : '',
            cellRendererFramework: VerifyCellRendererComponent,
            width                : 80,
            entity               : "rep"
        },
    ];

    public filter: { company, companies, type, sort, order, offset, limit, name, status } = {
        company  : "",
        companies: [],
        type     : "",
        sort     : "modified",
        order    : "desc",
        limit    : 14,
        offset   : 0,
        name     : "",
        status   : "",
    };

    public pageSizeOptions: number[] = [10, 14, 20, 50, 100];

    constructor(private router: Router,
                protected representativesService: RepresentativesService,
                public dialog: MatDialog) {
        this.gridOptions = <GridOptions>{
            context        : {
                componentParent: this
            },
            enableColResize: true,
            rowHeight      : 55
        };
    }

    ngOnInit() {
        this.getData();
        this.companies = this.companyAutoComplete.valueChanges.pipe(
            startWith(null),
            debounceTime(200),
            distinctUntilChanged(),
            switchMap(val => {
                if (isObject(val) || val == '') {
                    console.log('got Representative object', val);
                    if (isObject(val)) {
                        this.filter.company = (<Representative>val).company;
                        this.filter.companies.push((<Representative>val).company);
                    } else {
                        this.filter.company = val;
                    }
                    this.getData();
                    return Observable.empty();
                } else {
                    val = val || '';
                    return this.representativesService.fetchCompanies('name=' + encodeURIComponent(val))
                        .pipe(
                            map((response) => {
                                return response.filter((option) => {
                                    return option.company.toLowerCase().indexOf(val.toLowerCase()) !== -1
                                })
                            })
                        )
                }
            })
        );
    }

    protected getData() {
        this.loading = true;
        this.representativesService.list(this.filter).subscribe((data) => {
            this.loading         = false;
            this.representatives = data.data;
            this.total           = data.total;
            setTimeout(() => {
                if (!this.gridOptions.api) {
                    console.error('something strange here!', this);
                } else {
                    this.gridOptions.api.setRowData(this.representatives);
                    this.ngAfterViewInit()
                }
            }, 10);
        });
    }


    ngAfterViewInit(): void {
        setTimeout(() => {
            this.gridOptions.api.sizeColumnsToFit();
        });
    }

    displayCompanyFn(representative?: Representative): string | undefined {
        return representative ? representative.company : undefined;
    }

    onFilterChange() {
        this.filter.offset = 0;
        this.getData();
    }

    export() {
        let modalSettings = {
            total: this.total,
            from : this.filter.offset
        };
        let dialogRef     = this.dialog.open(ExportModalComponent, {
            height: '300px',
            width : '400px',
            data  : {
                settings: modalSettings
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            console.log('The dialog was closed', result);
            let exportSettings    = Object.assign({}, this.filter);
            exportSettings.limit  = result.total;
            exportSettings.offset = result.from;
            console.log('Export settings', exportSettings);
            let params           = new HttpParams({fromObject: exportSettings});
            window.location.href = "/data/representative-export?" + params;
        });
    }

    processPageEvent($event: PageEvent) {
        console.log('paging event', $event.pageIndex);
        this.filter.offset = this.filter.limit * $event.pageIndex;
        this.filter.limit = $event.pageSize;
        this.getData();
    }

    delete(id) {
        this.representativesService.delete(id).subscribe((response) => {
            if (response['success']) {
                this.getData();
            }
        });
    }

    deleteAndBlock(id) {
        this.representativesService.deleteAndBlock(id).subscribe((response) => {
            if (response['success']) {
                this.getData();
            }
        });
    }

    public verify(id) {
        this.representativesService.verify(id).subscribe((response) => {
            console.log('got response after verify', response);
            if (response['success']) {
                this.getData();
            } else {
                console.error(response['error']);
            }
        });
    }

    removeCompanyFromFilter(company: string) {
        this.filter.companies = this.filter.companies.filter((name) => {
            return name != company;
        });
        this.getData();
    }
}
