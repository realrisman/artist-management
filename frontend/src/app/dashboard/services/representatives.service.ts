import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Representative} from '../models/representative';

@Injectable()
export class RepresentativesService {

    protected representativesUrl  = "/data/representatives";
    protected managersUrl         = "/data/managers";
    protected agentsUrl           = "/data/agents";
    protected publicistsUrl       = "/data/publicists";
    protected companiesUrl        = "/data/companies";
    protected listUrl             = "/data/representative";
    protected saveUrl             = "/data/representative";
    protected checkEmailUrl       = "/data/check-email";
    protected importUrl           = "/data/representative-import";
    protected deleteUrl           = "/data/representative-delete";
    protected deleteAndBlockUrl   = "/data/representative-delete-block";
    protected logUrl              = "/data/representative-log";
    protected featuredUrl         = "/data/upload-featured";
    protected getVerifyUrl        = "/data/representative-need-verify";
    protected verifyUrl           = "/data/representative-verify/";
    protected deleteConnectionUrl = "/data/representative-connection-delete/";
    protected verifyConnectionUrl = "/data/representative-connection-verify/";


    constructor(protected http: HttpClient) {
    }

    public fetch(url: string, filter = '') {
        return this.http.get<any>(url + "?" + filter);
    }

    public fetchAgents(filter = '') {
        return this.fetch(this.agentsUrl, filter);
    }

    public fetchManagers(filter = '') {
        return this.fetch(this.managersUrl, filter);
    }

    public fetchPublicists(filter = '') {
        return this.fetch(this.publicistsUrl, filter);
    }

    public fetchCompanies(filter = '') {
        return this.fetch(this.companiesUrl, filter);
    }

    public fetchRepresentatives(filter = '') {
        return this.fetch(this.representativesUrl, filter);
    }

    public list(filter: any) {
        filter['companies[]'] = filter.companies;
        let params = new HttpParams({fromObject: filter});
        return this.fetch(this.listUrl, params.toString());
    }

    public fetchVerify(filter: any) {
        let params = new HttpParams({fromObject: filter});
        return this.fetch(this.getVerifyUrl, params.toString());
    }

    public verify(unid: number) {
      return this.http.post(this.verifyUrl + unid,'');
    }

    public details(id: string) {
        return this.fetch(this.listUrl + "/" + id);
    }

    public log(id: string) {
        return this.fetch(this.logUrl + "/" + id);
    }

    public fetchType(type: string, filter = '') {
        switch (type) {
            case Representative.PUBLICIST:
                return this.fetchPublicists(filter);
            case Representative.AGENT:
                return this.fetchAgents(filter);
            case Representative.MANAGER:
                return this.fetchManagers(filter);
            case 'company':
                return this.fetchCompanies(filter);
        }
    }

    public save(data: any) {
        return this.http.post(this.saveUrl, data);
    }

    public checkEmail(data: any) {
        return this.http.post(this.checkEmailUrl, data);
    }

    public delete(id) {
        return this.http.post(this.deleteUrl, {id: id});
    }
    public deleteAndBlock(id) {
        return this.http.post(this.deleteAndBlockUrl, {id: id});
    }

    public import(id) {
        return this.http.post(this.importUrl, {id: id});
    }
    public removeConnection(rc_id) {
        return this.http.post(this.deleteConnectionUrl+rc_id, '');
    }
    public verifyConnection(rc_id) {
        return this.http.post(this.verifyConnectionUrl+rc_id,'');
    }

    public featured(id, file) {

        let formData = new FormData();
        formData.append('file', file);
        formData.append('representative', id);

        return this.http.post(this.featuredUrl, formData);
    }
}
