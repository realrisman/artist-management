import { Component, OnInit } from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';
import {WP_URL} from '../../../config';

@Component({
  selector: 'app-representative-view-cell-renderer',
  templateUrl: './representative-view-cell-renderer.component.html',
  styleUrls: ['./representative-view-cell-renderer.component.css']
})
export class RepresentativeViewCellRendererComponent  implements OnInit, ICellRendererAngularComp {


    wp_url: string = WP_URL;

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

    showView() {
        console.log('show quick view for ', this.params);
    }
}