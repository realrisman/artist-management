import {Component, OnInit} from '@angular/core';
import {FormControl} from "@angular/forms";
import {GridOptions} from "ag-grid";
import {RepresentativeViewCellRendererComponent} from "../cell-renderers/representative-view-cell-renderer/representative-view-cell-renderer.component";
import {RepresentativeEditCellRendererComponent} from "../cell-renderers/representative-edit-cell-renderer/representative-edit-cell-renderer.component";
import {RepresentativeLogCellRendererComponent} from "../cell-renderers/representative-log-cell-renderer/representative-log-cell-renderer.component";
import {RepresentativeDeleteCellRendererComponent} from "../cell-renderers/representative-delete-cell-renderer/representative-delete-cell-renderer.component";
import {Router} from "@angular/router";
import {MatDialog} from "@angular/material/dialog";
import {Representative} from "../../models/representative";
import {PageEvent} from "@angular/material/paginator";
import {CompanyService} from "../../services/company.service";
import {CompanyEditCellRendererComponent} from "../cell-renderers/company-edit-cell-renderer/company-edit-cell-renderer.component";
import {VerifyCellRendererComponent} from "../cell-renderers/verify-cell-renderer/verify-cell-renderer.component";
import {CelebrityLogCellRendererComponent} from "../cell-renderers/celebrity-log-cell-renderer/celebrity-log-cell-renderer.component";
import {CompanyLogCellRendererComponent} from "../cell-renderers/company-log-cell-renderer/company-log-cell-renderer.component";

@Component({
    selector   : 'app-company-list',
    templateUrl: './company-list.component.html',
    styleUrls  : ['./company-list.component.css']
})
export class CompanyListComponent implements OnInit {
    companies        = <any>[];
    total            = 0;
    loading: boolean = false;

    companyAutoComplete = new FormControl();
    public gridOptions: GridOptions;

    columnDefs = [
        {
            headerName: '#',
            field     : 'id',
            width     : 55
        },
        {
            headerName: 'Name',
            field     : 'name'
        },
        {
            headerName: 'Verification Rank',
            field     : 'need_verify_flag'
        },
        {
            headerName           : '',
            cellRendererFramework: RepresentativeViewCellRendererComponent,
            width                : 80
        },
        {
            headerName           : '',
            cellRendererFramework: CompanyEditCellRendererComponent,
            width                : 80,
            from                 : "list"
        },
        // {
        //     headerName           : '',
        //     cellRendererFramework: RepresentativeDeleteCellRendererComponent,
        //     width                : 80
        // },
        {
            headerName           : '',
            cellRendererFramework: CompanyLogCellRendererComponent,
            width                : 45
        },
        {
            headerName           : '',
            cellRendererFramework: VerifyCellRendererComponent,
            width                : 80,
            entity               : "rep"
        },
    ];

    public filter: { sort, order, offset, limit, name, status } = {
        sort  : "modified",
        order : "desc",
        limit : 14,
        offset: 0,
        name  : "",
        status: "",
    };

    public pageSizeOptions: number[] = [10, 14, 20, 50, 100];

    constructor(private router: Router,
                protected companyService: CompanyService,
                public dialog: MatDialog) {
        this.gridOptions = <GridOptions>{
            context        : {
                componentParent: this
            },
            enableColResize: true,
            rowHeight      : 55,
        };
    }

    ngOnInit() {
        this.getData();
    }

    protected getData() {
        this.loading = true;
        this.companyService.list(this.filter).subscribe((data) => {
            this.loading   = false;
            this.companies = data.data;
            this.total     = data.total;
            console.log('got companies', this.companies);
            setTimeout(() => {
                if (!this.gridOptions.api) {
                    console.error('something strange here!', this);
                } else {
                    this.gridOptions.api.setRowData(this.companies);
                    this.ngAfterViewInit()
                }
            }, 10);
        });
    }


    ngAfterViewInit(): void {
        setTimeout(() => {
            console.log('columns fitted');
            this.gridOptions.api.sizeColumnsToFit();
        });
    }

    onFilterChange() {
        this.filter.offset = 0;
        this.getData();
    }

    processPageEvent($event: PageEvent) {
        console.log('paging event', $event.pageIndex);
        this.filter.offset = this.filter.limit * $event.pageIndex;
        this.filter.limit  = $event.pageSize;
        this.getData();
    }

    delete(id) {
        this.companyService.delete(id).subscribe((response) => {
            if (response['success']) {
                this.getData();
            }
        });
    }

    public verify(id) {
        this.companyService.verify(id).subscribe((response) => {
            console.log('got response after verify', response);
            if (response['success']) {
                this.getData();
            } else {
                console.error(response['error']);
            }
        });
    }

}
