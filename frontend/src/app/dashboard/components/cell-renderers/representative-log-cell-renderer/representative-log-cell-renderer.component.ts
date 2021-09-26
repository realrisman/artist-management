import { Component, OnInit } from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
  selector: 'app-representative-log-cell-renderer',
  templateUrl: './representative-log-cell-renderer.component.html',
  styleUrls: ['./representative-log-cell-renderer.component.css']
})
export class RepresentativeLogCellRendererComponent implements OnInit, ICellRendererAngularComp {


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
}

