import {Component, OnInit} from '@angular/core';
import {AuthService} from '../../../common/services/auth.service';

@Component({
    selector   : 'app-login',
    templateUrl: './login.component.html',
    styleUrls  : ['./login.component.css']
})
export class LoginComponent implements OnInit {

    public isSaving = false;
    public login    = '';
    public password = '';


    constructor(protected authService: AuthService) {
        console.log(1, authService);
    }

    ngOnInit() {
    }

    public onSubmit($event) {
        $event.preventDefault();
        this.isSaving = true;
        console.log(this.authService);
        this.authService.login(this.login, this.password).subscribe((val) => {
            console.log('return value from login function', val)
        });
    }
}
