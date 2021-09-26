import { Component, OnInit } from '@angular/core';
import {ActivatedRoute, ParamMap, Router} from "@angular/router";
import {Company} from "../../models/company";
import {CompanyService} from "../../services/company.service";

@Component({
  selector: 'app-company-log',
  templateUrl: './company-log.component.html',
  styleUrls: ['./company-log.component.css']
})
export class CompanyLogComponent implements OnInit {
    public companies = <any>[];
    public loading   = false;
    public verification_log = <any>[];


    constructor(protected service: CompanyService,
                private route: ActivatedRoute,
                private router: Router,) {

    }

    ngOnInit() {
        this.route.paramMap.switchMap((params: ParamMap) => {
            console.log('params.get(\'id\')', params.get('id'));
            this.loading = true;
            this.service.details(params.get('id')).subscribe((data) => {
                let company = <Company>data;
                this.verification_log = company.verification_log;
            });
            return this.service.log(params.get('id'));

        }).subscribe((data) => {
            this.companies = data;
            this.loading   = false;
        });
    }

    ngAfterViewInit(): void {
    }

    onRowClicked($event) {
        this.router.navigate(["/company", $event.data.id]);
    }}
