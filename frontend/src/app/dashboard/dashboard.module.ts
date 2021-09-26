import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';

import {DashboardRoutingModule} from './dashboard-routing.module';
import {CelebrityListComponent} from './components/celebrity-list/celebrity-list.component';
import {CelebrityService} from './services/celebrity.service';
import {UserListComponent} from './components/user-list/user-list.component';
import {UsersService} from './services/users.service';
import {AgGridModule} from 'ag-grid-angular';
import {MatCardModule} from '@angular/material/card';
import {UserEditComponent} from './components/user-edit/user-edit.component';
import {
    MAT_DATE_LOCALE,
    MatAutocompleteModule,
    MatCheckboxModule,
    MatChipsModule,
    MatDatepickerModule,
    MatExpansionModule,
    MatGridListModule,
    MatIconModule,
    MatInputModule,
    MatListModule,
    MatPaginatorModule,
    MatProgressBarModule,
    MatRadioModule,
    MatSelectModule,
    MatSnackBarModule, MatTableModule,
    MatTabsModule
} from '@angular/material';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MatButtonModule} from '@angular/material/button';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';
import {RepeatPasswordDirective} from './directives/repeat-password.directive';
import {CelebrityQuickViewCellRendererComponent} from './components/cell-renderers/celebrity-quick-view-cell-renderer/celebrity-quick-view-cell-renderer.component';
import {AgGridCellWrapperComponent} from './components/cell-renderers/ag-grid-cell-wrapper/ag-grid-cell-wrapper.component';
import {FlexLayoutModule} from '@angular/flex-layout';
import {RepresentativesService} from './services/representatives.service';
import {CelebrityViewCellRendererComponent} from './components/cell-renderers/celebrity-view-cell-renderer/celebrity-view-cell-renderer.component';
import {CelebrityEditCellRendererComponent} from './components/cell-renderers/celebrity-edit-cell-renderer/celebrity-edit-cell-renderer.component';
import {CelebrityDeleteCellRendererComponent} from './components/cell-renderers/celebrity-delete-cell-renderer/celebrity-delete-cell-renderer.component';
import {CelebrityLogCellRendererComponent} from './components/cell-renderers/celebrity-log-cell-renderer/celebrity-log-cell-renderer.component';
import {CelebrityEditComponent} from './components/celebrity-edit/celebrity-edit.component';
import {CelebrityLogComponent} from './components/celebrity-log/celebrity-log.component';
import {RepresentativeListComponent} from './components/representative-list/representative-list.component';
import {RepresentativeEditCellRendererComponent} from './components/cell-renderers/representative-edit-cell-renderer/representative-edit-cell-renderer.component';
import {RepresentativeLogCellRendererComponent} from './components/cell-renderers/representative-log-cell-renderer/representative-log-cell-renderer.component';
import {RepresentativeEditComponent} from './components/representative-edit/representative-edit.component';
import {RepresentativeLogComponent} from './components/representative-log/representative-log.component';
import {CategoryService} from './services/category.service';
import {AllowedDirective} from './directives/allowed.directive';
import {LogListComponent} from './components/log-list/log-list.component';
import {LogService} from './services/log.service';
import {LogDeleteCellRendererComponent} from './components/cell-renderers/log-delete-cell-renderer/log-delete-cell-renderer.component';
import {RepresentativeDeleteCellRendererComponent} from './components/cell-renderers/representative-delete-cell-renderer/representative-delete-cell-renderer.component';
import {EditorModule} from '@tinymce/tinymce-angular';
import {CelebrityVerboseLogComponent} from './components/celebrity-verbose-log/celebrity-verbose-log.component';
import {RepresentativeVerboseLogComponent} from './components/representative-verbose-log/representative-verbose-log.component';
import {RepresentativeViewCellRendererComponent} from './components/cell-renderers/representative-view-cell-renderer/representative-view-cell-renderer.component';
import {RepresentativeDeleteModalComponent} from './components/representative-delete-modal/representative-delete-modal.component';
import {MatDialogModule} from "@angular/material/dialog";
import {VerifyCellRendererComponent} from "./components/cell-renderers/verify-cell-renderer/verify-cell-renderer.component";
import {ExportModalComponent} from './components/export-modal/export-modal.component';
import {MessageModalComponent} from './components/message-modal/message-modal.component';
import {MatButtonToggleModule} from "@angular/material/button-toggle";
import {CompanyListComponent} from './components/company-list/company-list.component';
import {CompanyService} from "./services/company.service";
import {CompanyEditCellRendererComponent} from './components/cell-renderers/company-edit-cell-renderer/company-edit-cell-renderer.component';
import {CompanyEditComponent} from './components/company-edit/company-edit.component';
import {CompanyLogComponent} from './components/company-log/company-log.component';
import {CompanyVerboseLogComponent} from './components/company-verbose-log/company-verbose-log.component';
import {CompanyLogCellRendererComponent} from './components/cell-renderers/company-log-cell-renderer/company-log-cell-renderer.component';
import {Nl2brPipe} from './pipes/nl2br.pipe';

import {MatTooltipModule} from "@angular/material/tooltip";

@NgModule({
    imports        : [
        CommonModule,
        DashboardRoutingModule,
        MatCardModule,
        MatInputModule,
        MatButtonModule,
        FormsModule,
        ReactiveFormsModule,
        FlexLayoutModule,
        MatAutocompleteModule,
        MatSelectModule,
        MatProgressSpinnerModule,
        MatProgressBarModule,
        MatIconModule,
        MatTabsModule,
        MatPaginatorModule,
        MatGridListModule,
        MatDatepickerModule,
        MatTableModule,
        MatListModule,
        MatSnackBarModule,
        MatChipsModule,
        EditorModule,
        MatRadioModule,
        MatCheckboxModule,
        MatExpansionModule,
        AgGridModule.withComponents([
            CelebrityQuickViewCellRendererComponent,
            CelebrityDeleteCellRendererComponent,
            CelebrityEditCellRendererComponent,
            CelebrityLogCellRendererComponent,
            CelebrityViewCellRendererComponent,
            RepresentativeViewCellRendererComponent,
            RepresentativeEditCellRendererComponent,
            RepresentativeLogCellRendererComponent,
            RepresentativeDeleteCellRendererComponent,
            LogDeleteCellRendererComponent,
            CompanyEditCellRendererComponent,
            VerifyCellRendererComponent,
            CompanyLogCellRendererComponent,
        ]),
        MatDialogModule,
        MatButtonToggleModule,
        MatTooltipModule
    ],
    providers      : [
        CelebrityService,
        UsersService,
        RepresentativesService,
        CategoryService,
        LogService,
        CompanyService,
        {provide: MAT_DATE_LOCALE, useValue: 'en-US'}
    ],
    declarations   : [CelebrityListComponent,
        UserListComponent,
        UserEditComponent,
        RepeatPasswordDirective,
        CelebrityQuickViewCellRendererComponent,
        AgGridCellWrapperComponent,
        CelebrityViewCellRendererComponent,
        CelebrityEditCellRendererComponent,
        CelebrityDeleteCellRendererComponent,
        CelebrityLogCellRendererComponent,
        CelebrityEditComponent,
        CelebrityLogComponent,
        RepresentativeListComponent,
        RepresentativeEditCellRendererComponent,
        RepresentativeLogCellRendererComponent,
        RepresentativeEditComponent,
        RepresentativeLogComponent,
        AllowedDirective,
        LogListComponent,
        LogDeleteCellRendererComponent,
        RepresentativeDeleteCellRendererComponent,
        CelebrityVerboseLogComponent,
        RepresentativeVerboseLogComponent,
        RepresentativeViewCellRendererComponent,
        RepresentativeDeleteModalComponent,
        VerifyCellRendererComponent,
        ExportModalComponent,
        MessageModalComponent,
        CompanyListComponent,
        CompanyEditCellRendererComponent,
        CompanyEditComponent,
        CompanyLogComponent,
        CompanyVerboseLogComponent,
        CompanyLogCellRendererComponent,
        Nl2brPipe,
    ],
    entryComponents: [
        RepresentativeDeleteModalComponent,
        ExportModalComponent,
        MessageModalComponent
    ],
    exports        : [
        AllowedDirective
    ]
})
export class DashboardModule {
}
