import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';

@Component({
    selector   : 'app-celebrity-delete-cell-renderer',
    templateUrl: './celebrity-delete-cell-renderer.component.html',
    styleUrls  : ['./celebrity-delete-cell-renderer.component.css']
})
export class CelebrityDeleteCellRendererComponent implements OnInit, ICellRendererAngularComp {


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
        if (confirm('Are You Sure You Want to Delete This Celebrity?')) {
            this.params.context.componentParent.delete(this.params.data.id);
        }
    }
}
