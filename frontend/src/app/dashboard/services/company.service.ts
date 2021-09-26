import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders, HttpParams} from "@angular/common/http";

const httpOptions = {
    headers: new HttpHeaders({
        'Content-Type': 'application/json',
    })
};

@Injectable()
export class CompanyService {
    protected companyUrl          = "/data/company";
    protected listUrl             = "/data/company";
    protected autocompleteUrl     = "/data/companies";
    protected verifyUrl           = "/data/company-verify/";
    protected deleteUrl           = "/data/company";
    protected saveUrl             = "/data/company";
    protected checkEmailUrl       = "/data/check-email";
    protected importUrl           = "/data/company-import";
    protected logUrl              = "/data/company-log/";
    protected featuredUrl         = "/data/upload-featured";
    protected mergeUrl            = "/data/company-merge";
    protected deleteConnectionUrl = "/data/company-connection-delete/";

    constructor(protected http: HttpClient) {
    }

    public fetch(url: string, filter = '') {
        return this.http.get<any>(url + "?" + filter);
    }

    public list(filter: any) {
        let params = new HttpParams({fromObject: filter});
        return this.fetch(this.listUrl, params.toString());
    }

    public merge(input: any) {
        return this.http.post(this.mergeUrl, input);
    }

    public fetchCompaniesForAutocompleteOnCelebrityPage(filter = '') {
        return this.fetch(this.autocompleteUrl, filter);
    }

    public verify(unid: number) {
        return this.http.post(this.verifyUrl + unid, '');
    }

    public delete(id) {
        return this.http.delete(this.deleteUrl + "/" + id);
    }

    public save(data: any) {
        return this.http.post(this.saveUrl, data);
    }

    public checkEmail(data: any) {
        return this.http.post(this.checkEmailUrl, data);
    }

    public details(id: string) {
        return this.fetch(this.listUrl + "/" + id);
    }

    public import(id) {
        return this.http.post(this.importUrl, {id: id});
    }

    public log(unid: string) {
        return this.http.get(this.logUrl + unid, httpOptions);
    }

    public removeConnection(rc_id) {
        return this.http.post(this.deleteConnectionUrl + rc_id, '');
    }

    public featured(id, file) {

        let formData = new FormData();
        formData.append('file', file);
        formData.append('company', id);

        return this.http.post(this.featuredUrl, formData);
    }

}
