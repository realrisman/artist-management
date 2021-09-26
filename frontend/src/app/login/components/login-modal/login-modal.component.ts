import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material';
import {AuthService} from '../../../common/services/auth.service';

@Component({
    selector   : 'app-login-modal',
    templateUrl: './login-modal.component.html',
    styleUrls  : ['./login-modal.component.css']
})
export class LoginModalComponent implements OnInit {

    public isSaving = false;
    public login    = '';
    public password = '';
    public message  = '';

    constructor(public dialogRef: MatDialogRef<LoginModalComponent>,
                @Inject(MAT_DIALOG_DATA) public data: any, protected service: AuthService) {
    }

    ngOnInit() {
        console.log('data into dialog', this.data);
        this.message = this.data.message;
    }

    onSubmit($event) {
        this.isSaving = true;
        this.message  = 'Loading...';
        this.service.login(this.login, this.password).subscribe((response) => {

            this.isSaving = false;
            this.dialogRef.close(response);
        }, (err) => {
            this.isSaving = false;
            console.log('error login');
            this.message = err.error.message;
        });
    }
}
