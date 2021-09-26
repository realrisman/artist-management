import { Injectable } from "@angular/core";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Router } from "@angular/router";
import { ReplaySubject } from "rxjs/ReplaySubject";

const httpOptions = {
    headers: new HttpHeaders({
        "Content-Type": "application/json",
        // 'Authorization': 'my-auth-token'
    }),
};

@Injectable()
export class AuthService {
    protected role: string | boolean = false;

    protected roleSubject: ReplaySubject<boolean | string>;

    protected loginUrl = "login";

    constructor(protected http: HttpClient, protected router: Router) {}

    public login(login: string, password: string) {
        console.log("trying to log in", login, password);
        let payload = {
            login: login,
            password: password,
        };
        let result = new ReplaySubject();
        this.http.post(this.loginUrl, payload, httpOptions).subscribe(
            (response) => {
                if (response["success"]) {
                    this.role = response["role"];
                    this.roleSubject.next(this.role);
                    result.next(true);
                } else {
                    result.next(false);
                }
            },
            (err) => {
                result.error(err);
            }
        );

        return result;
    }

    public logout() {
        this.http.post("logout", {}).subscribe((response) => {
            this.role = false;
            console.log("redirecting?");
            this.router.navigate(["/"], {
                queryParams: { logout: Math.random().toString(10) },
            });

            window.location.reload();
        });
    }

    public getRole() {
        if (!this.role) {
            if (this.roleSubject == null) {
                this.roleSubject = new ReplaySubject<boolean | string>(1);
                this.http
                    .post(this.loginUrl, {}, httpOptions)
                    .subscribe((response) => {
                        if (response["success"]) {
                            this.role = response["role"];
                            this.roleSubject.next(this.role);
                        }
                    });
            }
        }
        return this.roleSubject;
    }
}
