import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
    selector   : 'app-log-delete-cell-renderer',
    templateUrl: './log-delete-cell-renderer.component.html',
    styleUrls  : ['./log-delete-cell-renderer.component.css']
})
export class LogDeleteCellRendererComponent implements OnInit, ICellRendererAngularComp {


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

    delete() {
        console.log('delete view for ', this.params);
        this.params.context.componentParent.deleteEntity(this.params.data.id);
    }
}