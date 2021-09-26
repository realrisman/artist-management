import {Component, OnInit} from '@angular/core';
import {UsersService} from '../../services/users.service';
import {GridOptions} from 'ag-grid';
import {Router} from '@angular/router';
import {AuthService} from '../../../common/services/auth.service';

@Component({
    selector   : 'app-user-list',
    templateUrl: './user-list.component.html',
    styleUrls  : ['./user-list.component.css']
})
export class UserListComponent implements OnInit {
    protected roles = {
        ROLE_ADMIN         : 'Administrator',
        ROLE_EDITOR        : 'Editor',
        ROLE_SPECTATOR     : 'Spectator',
        ROLE_SPOT_CHECKER  : 'Spot Checker',
        ROLE_TRAINER       : 'Trainer',
        ROLE_WRITER        : 'Writer',
        ROLE_IMAGE_UPLOADER: 'Image Uploader'
    };

    users = <any>[];

    public gridOptions: GridOptions;

    columnDefs = [
        {
            headerName: '#',
            field     : 'id',
            width     : 35
        },
        {
            headerName: 'Login',
            field     : 'login'
        },
        {
            headerName: 'First name',
            field     : 'first_name'
        },
        {
            headerName: 'Last name',
            field     : 'last_name'
        },
        {
            headerName: 'Monthly export limit',
            field     : 'monthly_limit',
            cellRenderer: (params) => {
              return params.data.monthly_limit ? params.data.monthly_limit : 'Unlimited'
            }
        },
        {
            headerName: 'Limit used',
            field     : 'limit_used'
        },
        {
            headerName  : 'Deleted',
            field       : 'deleted',
            cellRenderer: (params) => {
                return params.data.deleted ? 'Deleted' : 'Active'
            }
        },
        {
            headerName  : 'Role',
            field       : 'role',
            cellRenderer: (params) => {
                return this.roles[params.data.role]
            }
        }
    ];


    constructor(protected service: UsersService, private router: Router, protected auth: AuthService) {
        this.gridOptions = <GridOptions>{
            context: {
                componentParent: this
            }
        };
    }

    ngOnInit() {
        this.service.fetch().subscribe((data) => {
            this.users = data;
            setTimeout(() => {
                if (!this.gridOptions.api) {
                    console.error('something strange here!', this);
                } else {
                    this.gridOptions.api.setRowData(this.users);
                    this.ngAfterViewInit()
                }
            }, 10);
        }, (error) => {
            console.log('list component error', error);
        });
    }


    ngAfterViewInit(): void {
        setTimeout(() => {
            this.gridOptions.api.sizeColumnsToFit();
        });
    }

    onRowClicked($event) {
        console.log('row clicked', $event);
        this.router.navigate(["/users", $event.data.id]);
    }
}
