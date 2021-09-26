import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders, HttpParams} from '@angular/common/http';
import {catchError} from 'rxjs/operators';
import {CelebrityFilter} from '../models/celebrity-filter';
import {RequestOptions} from '@angular/http';

const httpOptions = {
    headers: new HttpHeaders({
        'Content-Type': 'application/json',
    })
};

@Injectable()
export class CelebrityService {


    protected listUrl = "/data/celebrity-quick";
    protected quickUrl = "/data/celebrity-quick";
    protected fullUrl = "/data/celebrity-full";
    protected getVerifyUrl = "/data/celebrity-need-verify";
    protected verifyUrl = "/data/celebrity-verify/";
    protected detailsUrl = "/data/celebrity/";
    protected saveUrl = "/data/celebrity";
    protected checkEmailUrl = "/data/check-email";
    protected saveImageUploaderUrl = "/data/celebrity-image-uploader";
    protected importUrl = "/data/celebrity-import";
    protected deleteUrl = "/data/celebrity-delete";
    protected logUrl = "/data/celebrity-log/";
    protected featuredUrl = "/data/upload-featured";

    constructor(protected http: HttpClient) {
    }

    protected getParams(data: any) {
        let params = new HttpParams({fromObject:data});

        return params;
    }

    public fetchList(filter?: CelebrityFilter) {
        let params = this.getParams(filter.toJson());

        return this.http.get(this.listUrl + '?' + params, httpOptions)
    }

    public fetchVerify(filter?: CelebrityFilter) {
        let params = this.getParams(filter.toJson());

        return this.http.get(this.getVerifyUrl + '?' + params, httpOptions)
    }

    public fetchQuick(filter?: CelebrityFilter) {
        let params = this.getParams(filter.toJson());

        return this.http.get(this.quickUrl + '?' + params, httpOptions)
    }

    public fetchFull(filter?: CelebrityFilter) {
        let params = this.getParams(filter.toJson());

        return this.http.get(this.fullUrl + '?' + params, httpOptions)
    }

    public details(unid: string) {
        return this.http.get(this.detailsUrl + unid, httpOptions);
    }

    public log(unid: string) {
        return this.http.get(this.logUrl + unid, httpOptions);
    }

    public verify(unid: string) {
        return this.http.post(this.verifyUrl + unid, httpOptions);
    }

    public save(data: any) {
        return this.http.post(this.saveUrl, data);
    }
    public checkEmail(data: any) {
        return this.http.post(this.checkEmailUrl, data);
    }
    public saveUploader(data: any) {
        return this.http.post(this.saveImageUploaderUrl, data);
    }

    public delete(id) {
        return this.http.post(this.deleteUrl, {id: id});
    }

    public import(id) {
        return this.http.post(this.importUrl, {id: id});
    }

    public featured(id, file) {

        let formData = new FormData();
        formData.append('file', file);
        formData.append('celebrity', id);

        return this.http.post(this.featuredUrl, formData);
    }
}
