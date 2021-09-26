import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
    selector   : 'app-celebrity-edit-cell-renderer',
    templateUrl: './celebrity-edit-cell-renderer.component.html',
    styleUrls  : ['./celebrity-edit-cell-renderer.component.css']
})
export class CelebrityEditCellRendererComponent implements OnInit, ICellRendererAngularComp {


    public params: any;

    public from:string = "";

    constructor() {
    }

    agInit(params: any): void {
        this.params = params;
        this.from = params.colDef.from;
    }

    ngOnInit() {
    }

    refresh(): boolean {
        return false;
    }

    doEdit() {
        console.log('edit view for ', this.params);
    }
}
