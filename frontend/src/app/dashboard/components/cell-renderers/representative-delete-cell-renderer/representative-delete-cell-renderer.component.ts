import {Component, OnInit} from '@angular/core';
import {ICellRendererAngularComp} from 'ag-grid-angular';
import {MatDialog} from "@angular/material/dialog";
import {RepresentativeDeleteModalComponent} from "../../representative-delete-modal/representative-delete-modal.component";

@Component({
    selector   : 'app-representative-delete-cell-renderer',
    templateUrl: './representative-delete-cell-renderer.component.html',
    styleUrls  : ['./representative-delete-cell-renderer.component.css']
})
export class RepresentativeDeleteCellRendererComponent implements OnInit, ICellRendererAngularComp {

  public params: any;
  constructor(public dialog: MatDialog) {
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
    let dialogRef = this.dialog.open(RepresentativeDeleteModalComponent, {
      height: '140px',
      width: '400px'
    });
    dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed', result);
      if ('block' == result) {
        this.params.context.componentParent.deleteAndBlock(this.params.data.id);
      }
      if ('delete' == result) {
        this.params.context.componentParent.delete(this.params.data.id);
      }
    });
  }
}
