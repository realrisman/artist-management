import {Component, OnInit} from '@angular/core';

@Component({
    selector: 'app-verify-cell-renderer',
    templateUrl: './verify-cell-renderer.component.html',
    styleUrls: ['./verify-cell-renderer.component.css']
})
export class VerifyCellRendererComponent implements OnInit {

    public params: any;
    public loading:boolean = false;
    public shifted:boolean = false;

    constructor() {
    }

    ngOnInit(): void {
    }

    agInit(params: any): void {
        this.params = params;
        this.shifted = ("rep" != params.colDef.entity);
    }

    verify() {
        this.loading = true;
        this.params.context.componentParent.verify(this.params.data.id);
    }
}
