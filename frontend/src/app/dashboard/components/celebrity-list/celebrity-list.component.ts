import {Component, OnInit} from '@angular/core';
import {CelebrityService} from '../../services/celebrity.service';
import {GridOptions} from 'ag-grid';
import {Router} from '@angular/router';
import {FormControl} from '@angular/forms';
import {Observable} from 'rxjs/Observable';
import {debounceTime, distinctUntilChanged, map, startWith, switchMap} from 'rxjs/operators';
import {Representative} from '../../models/representative';
import {isObject} from 'util';
import {CelebrityFilter} from '../../models/celebrity-filter';
import {RepresentativesService} from '../../services/representatives.service';
import {CelebrityViewCellRendererComponent} from '../cell-renderers/celebrity-view-cell-renderer/celebrity-view-cell-renderer.component';
import {CelebrityEditCellRendererComponent} from '../cell-renderers/celebrity-edit-cell-renderer/celebrity-edit-cell-renderer.component';
import {CelebrityDeleteCellRendererComponent} from '../cell-renderers/celebrity-delete-cell-renderer/celebrity-delete-cell-renderer.component';
import {CelebrityLogCellRendererComponent} from '../cell-renderers/celebrity-log-cell-renderer/celebrity-log-cell-renderer.component';
import {PageEvent} from '@angular/material';
import {WP_URL} from '../../config';
import {HttpParams} from "@angular/common/http";
import {VerifyCellRendererComponent} from "../cell-renderers/verify-cell-renderer/verify-cell-renderer.component";
import {ExportModalComponent} from "../export-modal/export-modal.component";
import {MatDialog} from "@angular/material/dialog";

@Component({
    selector   : 'app-celebrities',
    templateUrl: './celebrity-list.component.html',
    styleUrls  : ['./celebrity-list.component.css']
})
export class CelebrityListComponent implements OnInit {

    wp_url: string = WP_URL;

    celebrities      = <any>[];
    total            = 0;
    loading: boolean = false;

    agentAutoComplete     = new FormControl();
    managerAutoComplete   = new FormControl();
    publicistAutoComplete = new FormControl();
    companyAutoComplete   = new FormControl();

    filtered: { agents, managers, publicists, companies } = {
        agents    : null,
        managers  : null,
        publicists: null,
        companies : null
    };

    public gridOptions: GridOptions;

    public filter: CelebrityFilter = new CelebrityFilter();

    public pageSizeOptions: number[] = [10, 20, 50, 100];

    public viewType: string = 'list';

    columnDefs = [
        {
            headerName: '#',
            field     : 'id',
            width     : 30
        },
        {
            headerName: 'Name',
            field     : 'name'
        },
        {
            headerName: 'Categories',
            field     : 'categories',
            width     : 100
        },
        {
            headerName  : 'Representatives',
            field       : 'reps',
            cellRenderer: (params) => {
                let cell = [];
                for (let rtype in params.data.representatives) {
                    if (params.data.representatives.hasOwnProperty(rtype)) {
                        cell.push(["<b>" + rtype + "</b>: " + params.data.representatives[rtype].join(", ")]);
                    }
                }
                return cell.join("<br>");
            }
        },
        {
            headerName: 'Status',
            field     : 'status',
            width     : 40
        },
        {
            headerName           : '',
            cellRendererFramework: CelebrityViewCellRendererComponent,
            width                : 45
        },
        {
            headerName           : '',
            cellRendererFramework: CelebrityEditCellRendererComponent,
            width                : 45,
            from: "list"
        },
        {
            headerName           : '',
            cellRendererFramework: CelebrityDeleteCellRendererComponent,
            width                : 45
        },
        {
            headerName           : '',
            cellRendererFramework: CelebrityLogCellRendererComponent,
            width                : 45
        },
        {
            headerName           : '',
            cellRendererFramework: VerifyCellRendererComponent,
            width                : 45
        }

    ];


    constructor(protected service: CelebrityService,
                private router: Router,
                protected representativesService: RepresentativesService,
                public dialog: MatDialog
    ) {
        this.gridOptions  = <GridOptions>{
            context        : {
                componentParent: this
            },
            enableColResize: true,
            rowHeight      : 62
        };
        this.filter.limit = 10;
    }

    ngOnInit() {
        this.getData();
        this.filtered['agents']     = this.generateAutoCompleteLoaderHandler(this.agentAutoComplete.valueChanges, Representative.AGENT);
        this.filtered['managers']   = this.generateAutoCompleteLoaderHandler(this.managerAutoComplete.valueChanges, Representative.MANAGER);
        this.filtered['publicists'] = this.generateAutoCompleteLoaderHandler(this.publicistAutoComplete.valueChanges, Representative.PUBLICIST);
        this.filtered['companies']  = this.companyAutoComplete.valueChanges.pipe(
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
                    console.log('switchmap', val);
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

    protected generateAutoCompleteLoaderHandler(observable: Observable<any>, type: string) {
        return observable.pipe(
            startWith(null),
            debounceTime(200),
            distinctUntilChanged(),
            switchMap(val => {
                if (isObject(val) || val == '') {
                    console.log('got Representative object', val);
                    if (isObject(val)) {
                        this.filter[type] = <Representative>val;
                    } else {
                        this.filter[type] = val;
                    }
                    this.getData();
                    return Observable.empty();
                } else {
                    val = val || '';
                    return this.representativesService.fetchType(type, 'name=' + encodeURIComponent(val))
                        .pipe(
                            map((response) => {
                                return response;
                            })
                        );
                }
            })
        );
    }

    protected getData() {
        this.loading = true;
        let datasource;
        switch (this.viewType) {
            case 'list':
            default:
                datasource = this.service.fetchList(this.filter);
                break;
            case 'quick':
                datasource = this.service.fetchQuick(this.filter);
                break;
            case 'full':
                datasource = this.service.fetchFull(this.filter);
                break;
        }
        datasource.subscribe((data) => {
            this.celebrities = data.data;
            this.total       = data.total;
            this.loading     = false;
            setTimeout(() => {
                if (!this.gridOptions.api) {
                    console.error('something strange here!', this);
                } else {
                    this.gridOptions.api.setRowData(this.celebrities);
                    this.ngAfterViewInit()
                }
            }, 10);
        });
    }


    displayFn(representative?: Representative): string | undefined {
        return representative ? representative.name : undefined;
    }

    displayCompanyFn(representative?: Representative): string | undefined {
        return representative ? representative.company : undefined;
    }

    ngAfterViewInit(): void {
        setTimeout(() => {
            this.gridOptions.api.sizeColumnsToFit();
        });
    }

    onRowClicked($event) {
        console.log('row clicked', $event);
        // this.router.navigate(["/user", $event.data.id]);
    }

    onFilterChange() {
        this.getData();
    }

    setViewType(type) {
        this.viewType = type;
        if (type == 'quick') {
            this.filter.limit = 12;
        } else {
            this.filter.limit = 10;
        }
        this.ngAfterViewInit();
        this.getData();
    }

    export(){
        let modalSettings = {
            total: this.total,
            from: this.filter.offset
        };
        let dialogRef = this.dialog.open(ExportModalComponent, {
            height: '300px',
            width: '400px',
            data:{
                settings: modalSettings
            }
        });
        dialogRef.afterClosed().subscribe(result => {
            console.log('The dialog was closed', result);
            let exportSettings = Object.assign({},<any>this.filter.toJson());
            exportSettings.limit = result.total;
            exportSettings.offset = result.from;
            console.log('Export settings', exportSettings);
            let params = new HttpParams({fromObject: exportSettings});
            window.location.href = "/data/celebrity-export?"+params;
        });
    }

    processPageEvent($event: PageEvent) {
        console.log('paging event', $event);
        this.filter.offset = this.filter.limit * $event.pageIndex;
        this.filter.limit = $event.pageSize;
        this.getData();
    }

    delete(id) {
        this.service.delete(id).subscribe((response) => {
            if (response['success']) {
                this.getData();
            }
        });
    }

    removeCompanyFromFilter(company: string) {
        this.filter.companies = this.filter.companies.filter((name) => {
            return name != company;
        });
        this.getData();
    }

    public verify(id) {
        this.service.verify(id).subscribe((response) => {
            console.log('got response after verify', response);
            if (response['success']) {
                this.getData();
            }else {
                console.error(response['error']);
            }
        });
    }
}
