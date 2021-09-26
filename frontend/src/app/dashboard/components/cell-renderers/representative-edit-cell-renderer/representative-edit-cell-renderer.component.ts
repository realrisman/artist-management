import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
    selector   : 'app-representative-edit-cell-renderer',
    templateUrl: './representative-edit-cell-renderer.component.html',
    styleUrls  : ['./representative-edit-cell-renderer.component.css']
})
export class RepresentativeEditCellRendererComponent implements OnInit, ICellRendererAngularComp {


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
}
