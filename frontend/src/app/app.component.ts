import {Component} from '@angular/core';
import {AuthService} from './common/services/auth.service';

@Component({
    selector   : 'app-root',
    templateUrl: './app.component.html',
    styleUrls  : ['./app.component.css']
})
export class AppComponent {
    title  = 'app';
    opened = true;

    constructor(protected service: AuthService) {

    }

    public logout() {
        this.service.logout();
    }
}
