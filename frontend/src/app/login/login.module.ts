import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';

import {LoginRoutingModule} from './login-routing.module';
import {LoginComponent} from './components/login/login.component';
import {MatDialogModule, MatInputModule, MatProgressBarModule} from '@angular/material';
import {MatButtonModule} from '@angular/material/button';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner'
import {FormsModule} from '@angular/forms';
import {AuthService} from '../common/services/auth.service';
import {LoginModalComponent} from './components/login-modal/login-modal.component';
import {HTTP_INTERCEPTORS} from '@angular/common/http';
import {AuthInterceptor} from '../common/services/auth.interceptor';

@NgModule({
    imports        : [
        CommonModule,
        LoginRoutingModule,
        MatInputModule,
        MatProgressSpinnerModule,
        MatDialogModule,
        MatButtonModule,
        MatProgressBarModule,
        FormsModule
    ],
    providers      : [
        AuthService,
        {
            provide : HTTP_INTERCEPTORS,
            useClass: AuthInterceptor,
            multi   : true
        }
    ],
    entryComponents: [
        LoginModalComponent
    ],
    declarations   : [LoginComponent, LoginModalComponent]
})
export class LoginModule {
}
