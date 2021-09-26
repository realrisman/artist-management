import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-company-edit-cell-renderer',
  templateUrl: './company-edit-cell-renderer.component.html',
  styleUrls: ['./company-edit-cell-renderer.component.css']
})
export class CompanyEditCellRendererComponent implements OnInit {

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
