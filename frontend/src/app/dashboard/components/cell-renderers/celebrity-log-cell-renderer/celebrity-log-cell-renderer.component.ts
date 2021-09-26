import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
    selector   : 'app-celebrity-log-cell-renderer',
    templateUrl: './celebrity-log-cell-renderer.component.html',
    styleUrls  : ['./celebrity-log-cell-renderer.component.css']
})
export class CelebrityLogCellRendererComponent implements OnInit, ICellRendererAngularComp {


    public params: any;

    constructor() {
    }

    agInit(params: any): void {
        this.params = params;
    }

    ngOnInit() {
    }

    refresh(): boolean {
        return false;
    }

    showLog() {
        console.log('show log view for ', this.params);
    }
}
