import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';

@Injectable()
export class CategoryService {
    protected listUrl = "/data/categories";

    constructor(protected http: HttpClient) {
    }

    public list(type:string = 'celebrities') {
        return this.http.get(this.listUrl+"?type="+type);
    }
}
