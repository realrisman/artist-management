import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
import {RepresentativesService} from '../../services/representatives.service';
import {Representative} from "../../models/representative";

@Component({
  selector: 'app-representative-log',
  templateUrl: './representative-log.component.html',
  styleUrls: ['./representative-log.component.css']
})
export class RepresentativeLogComponent implements OnInit {
  public loading = false;
  public representatives = <any>[];
  public verification_log = <any>[];

  constructor(protected service: RepresentativesService,
              private route: ActivatedRoute,
              private router: Router,) {

  }

  ngOnInit() {
    this.route.paramMap.switchMap((params: ParamMap) => {
      console.log('params.get(\'id\')', params.get('id'));
      this.loading = true;
      this.service.details(params.get('id')).subscribe((data) => {
        let representative = <Representative>data;
        this.verification_log = representative.verification_log;
      });
      return this.service.log(params.get('id'));

    }).subscribe((data) => {
      this.loading = false;
      this.representatives = data;

    });
  }

}
