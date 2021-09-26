import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {User} from '../models/user';
import {Observable} from 'rxjs/Observable';

@Injectable()
export class UsersService {

    protected listUrl           = "/data/users";
    protected filterListUrl     = "/data/user-list";
    protected emaillistUrl      = '/data/emails';
    protected emailVerfiySync   = '/data/emails-sync';
    protected emailReset        = '/data/emails-reset';
    protected stopRunningProcessUrl        = '/data/stop-running-process';

    constructor(protected http: HttpClient) {
    }

    public fetch() {
        return this.http.get(this.listUrl)
    }

    public short() {
        return this.http.get(this.filterListUrl)
    }

    public getUser(id: string) {
        return this.http.get(this.listUrl + "/" + id);
    }

    public save(user: User) {
        return this.http.post(this.listUrl, user.toJson());
    }

    public fetchEmails() {
        return this.http.get(this.emaillistUrl);
    }

    public resetEmails() {
        return this.http.get(this.emailReset);
    }

    public emailVerfiyProcess() {
        return this.http.get(this.emailVerfiySync);
    }

    public stopRunningProcess() {
        return this.http.get(this.stopRunningProcessUrl);
    }
}
