import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {Celebrity} from '../../models/celebrity';
import {CompanyService} from "../../services/company.service";
import {CelebrityService} from "../../services/celebrity.service";

@Component({
    selector   : 'app-celebrity-log',
    templateUrl: './celebrity-log.component.html',
    styleUrls  : ['./celebrity-log.component.css']
})
export class CelebrityLogComponent implements OnInit {
    public celebrities = <any>[];
    public loading     = false;
    public verification_log = <any>[];


    constructor(protected service: CelebrityService,
                private route: ActivatedRoute,
                private router: Router,) {

    }

    ngOnInit() {
        this.route.paramMap.switchMap((params: ParamMap) => {
            console.log('params.get(\'id\')', params.get('id'));
            this.loading = true;
            this.service.details(params.get('id')).subscribe((data) => {
              let celebrity = <Celebrity>data;
              this.verification_log = celebrity.verification_log;
            });
            return this.service.log(params.get('id'));

        }).subscribe((data) => {
            this.celebrities = data;
            this.loading     = false;
        });
    }

    ngAfterViewInit(): void {
    }

    onRowClicked($event) {
        this.router.navigate(["/celebrities", $event.data.id]);
    }
}
