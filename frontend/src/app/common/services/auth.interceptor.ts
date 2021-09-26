import {
    HttpEvent,
    HttpHandler,
    HttpInterceptor,
    HttpRequest,
} from "@angular/common/http";
import { Observable } from "rxjs/Observable";
import { MatDialog, MatSnackBar } from "@angular/material";
import { LoginModalComponent } from "../../login/components/login-modal/login-modal.component";
import { AuthService } from "./auth.service";
import { Injectable } from "@angular/core";
import "rxjs/add/operator/do";
import "rxjs/add/operator/catch";
import "rxjs/add/operator/map";
import "rxjs/add/observable/empty";
import "rxjs/add/observable/throw";
import { Subject } from "rxjs/Subject";

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
    refreshTokenInProgress = false;

    tokenRefreshedSource = new Subject();
    tokenRefreshed$ = this.tokenRefreshedSource.asObservable();

    constructor(
        protected auth: AuthService,
        public dialog: MatDialog,
        protected snackBar: MatSnackBar
    ) {}

    intercept(
        request: HttpRequest<any>,
        next: HttpHandler
    ): Observable<HttpEvent<any>> {
        return next.handle(request).catch((error) => {
            if (error.status === 403) {
                return this.refreshToken(error)
                    .switchMap(() => {
                        return next.handle(request);
                    })
                    .catch(() => {
                        console.log("failed login");
                        return Observable.empty();
                    });
            }
            if (error.status === 503 || error.status === 504) {
                this.snackBar.open("Server error " + error.status, "Ok", {
                    duration: 3000,
                    panelClass: "warning",
                });
                return next.handle(request);
            }

            return Observable.throw(error);
        });
    }

    showLoginModal(err) {
        return new Observable<any>((observer) => {
            let dialogRef = this.dialog.open(LoginModalComponent, {
                height: "330px",
                width: "400px",
                data: {
                    message: err.error.message,
                },
            });
            dialogRef.afterClosed().subscribe((result) => {
                console.log("The dialog was closed", result);
                observer.next(result);
            });
        });
    }

    refreshToken(err) {
        if (
            this.refreshTokenInProgress &&
            err.hasOwnProperty("url") &&
            err.url.indexOf("login") == -1
        ) {
            return new Observable((observer) => {
                this.tokenRefreshed$.subscribe(() => {
                    observer.next();
                    observer.complete();
                });
            });
        } else {
            this.refreshTokenInProgress = true;

            return this.showLoginModal(err).do(() => {
                console.log("setting refresh status to false");
                this.refreshTokenInProgress = false;
                this.tokenRefreshedSource.next();
            });
        }
    }
}
