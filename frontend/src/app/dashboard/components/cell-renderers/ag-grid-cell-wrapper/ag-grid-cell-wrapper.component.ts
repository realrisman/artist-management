import {Component, OnInit, Input} from '@angular/core';

@Component({
    selector   : 'ag-grid-cell-wrapper',
    templateUrl: './ag-grid-cell-wrapper.component.html',
    styleUrls  : ['./ag-grid-cell-wrapper.component.css']
})
export class AgGridCellWrapperComponent implements OnInit {

    @Input() fxLayoutAlign = 'start center';
    @Input() shifted       = false;

    constructor() {
    }

    ngOnInit() {
    }

}
