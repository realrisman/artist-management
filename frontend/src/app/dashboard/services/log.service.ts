import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import * as moment from 'moment';

@Injectable()
export class LogService {

    protected celebritiesUrl     = "/data/log-celebrities";
    protected representativesUrl = "/data/log-representatives";
    protected companiesUrl       = "/data/log-companies";

    constructor(protected http: HttpClient) {
    }

    protected normalizeFilter(filter: any): any {
        ['date','from','to'].forEach(function (field) {
            if (filter[field]) {
                filter[field] = moment(filter[field]).format('YYYY-MM-DD');
            } else if (filter[field] == null) {
                filter[field] = "";
            }
        });

        return filter;
    }

    public celebrities(filter: any) {
        filter     = this.normalizeFilter(filter);
        let params = new HttpParams({fromObject: filter});
        return this.http.get(this.celebritiesUrl + "?" + params.toString());
    }

    public representatives(filter: any) {
        filter     = this.normalizeFilter(filter);
        let params = new HttpParams({fromObject: filter});
        return this.http.get(this.representativesUrl + "?" + params.toString());
    }

    public companies(filter: any) {
        filter     = this.normalizeFilter(filter);
        let params = new HttpParams({fromObject: filter});
        return this.http.get(this.companiesUrl + "?" + params.toString());
    }

    public deleteCelebrityLog(id) {
        return this.http.post(this.celebritiesUrl + "-delete", {id: id});
    }

    public deleteRepresentativeLog(id) {
        return this.http.post(this.representativesUrl + "-delete", {id: id});
    }

    public deleteCompanyLog(id) {
        return this.http.post(this.companiesUrl + "-delete", {id: id});
    }
}
