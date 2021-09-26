import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
    selector   : 'app-celebrity-quick-view-cell-renderer',
    templateUrl: './celebrity-quick-view-cell-renderer.component.html',
    styleUrls  : ['./celebrity-quick-view-cell-renderer.component.css']
})
export class CelebrityQuickViewCellRendererComponent implements OnInit, ICellRendererAngularComp {

    constructor() {
    }

    public params: any;

    agInit(params: any): void {
        this.params = params;
    }

    ngOnInit() {
    }

    refresh(): boolean {
        return false;
    }

    showQuickView() {
        console.log('show quick view for ', this.params);
    }
}
