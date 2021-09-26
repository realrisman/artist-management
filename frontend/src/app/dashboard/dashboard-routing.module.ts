import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {CelebrityListComponent} from './components/celebrity-list/celebrity-list.component';
import {UserListComponent} from './components/user-list/user-list.component';
import {UserEditComponent} from './components/user-edit/user-edit.component';
import {CelebrityEditComponent} from './components/celebrity-edit/celebrity-edit.component';
import {CelebrityLogComponent} from './components/celebrity-log/celebrity-log.component';
import {RepresentativeListComponent} from './components/representative-list/representative-list.component';
import {RepresentativeEditComponent} from './components/representative-edit/representative-edit.component';
import {RepresentativeLogComponent} from './components/representative-log/representative-log.component';
import {LogListComponent} from './components/log-list/log-list.component';
import {CompanyListComponent} from "./components/company-list/company-list.component";
import {CompanyEditComponent} from "./components/company-edit/company-edit.component";
import {CompanyLogComponent} from "./components/company-log/company-log.component";

const routes: Routes = [
    {
        path     : "celebrities",
        component: CelebrityListComponent
    },
    {
        path     : "users",
        component: UserListComponent,
    },
    {
        path     : "logs",
        component: LogListComponent,
    },
    {
        path     : "representatives",
        component: RepresentativeListComponent,
    },
    {
        path     : "companies",
        component: CompanyListComponent,
    },
    {
        path     : "users/:id",
        component: UserEditComponent
    },
    {
        path     : "celebrities/log/:id",
        component: CelebrityLogComponent
    },
    {
        path     : "celebrities/:id",
        component: CelebrityEditComponent
    },
    {
        path     : "representatives/:id",
        component: RepresentativeEditComponent
    },
    {
        path     : "companies/:id",
        component: CompanyEditComponent
    },
    {
        path     : "companies/log/:id",
        component: CompanyLogComponent
    },
    {
        path     : "representatives/log/:id",
        component: RepresentativeLogComponent
    },
    {
        path      : '',
        redirectTo: 'celebrities',
        pathMatch : 'full'
    },

];

@NgModule({
    imports: [RouterModule.forRoot(routes, {enableTracing: false})],
    exports: [RouterModule]
})
export class DashboardRoutingModule {
}
