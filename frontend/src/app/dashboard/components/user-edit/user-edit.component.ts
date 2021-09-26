import {Component, OnInit, ViewChild} from '@angular/core';
import 'rxjs/add/operator/switchMap';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {UsersService} from '../../services/users.service';
import {User} from '../../models/user';
import {NgForm} from '@angular/forms';
import {Observable} from 'rxjs/Observable';
import {Subscriber} from 'rxjs/Subscriber';


@Component({
    selector   : 'app-user-edit',
    templateUrl: './user-edit.component.html',
    styleUrls  : ['./user-edit.component.css']
})
export class UserEditComponent implements OnInit {

    public isSaving: boolean = true;
    public user: User        = new User(0, '', '');
    public roles             = User.roles;
    public errors: string[];

    constructor(protected service: UsersService,
                private route: ActivatedRoute,
                private router: Router,) {
    }

    ngOnInit() {
        this.route.paramMap.switchMap((params: ParamMap) => {
            console.log('params.get(\'id\')', params.get('id'));
            if ("add" == params.get('id')) {
                return new Observable<any>((subscriber: Subscriber<any>) => subscriber.next({
                    "id"        : null,
                    "login"     : "",
                    "first_name": "",
                    "last_name" : "",
                    "password"  : "",
                    "role"      : "ROLE_SPECTATOR"
                }));
            } else {
                return this.service.getUser(params.get('id'));
            }
        }).subscribe((data) => {
            console.log(data);
            this.isSaving = false;
            this.user     = new User(parseInt(data['id']), data['login'], data['role'], '', data['first_name'], data['last_name'], data['deleted'], data['monthly_limit'], data['limit_used']);
            console.log(this.user);
        });

    }

    onSubmit($event) {
        console.log(this.user);
        this.errors   = [];
        this.isSaving = true;
        this.service.save(this.user).subscribe((response) => {
            this.isSaving = false;
            console.log('response after save', response);
            if (!response['success']) {
                //@TODO maybe place errors near corresponding inputs?
                Object.keys(response['errors']).forEach((error) => {
                    this.errors.push(response['errors'][error].text);
                })
            } else {
                this.router.navigate(['/users'])
            }
        })
    }
}
