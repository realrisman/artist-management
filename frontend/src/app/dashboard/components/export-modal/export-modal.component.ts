import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from "@angular/material/dialog";

@Component({
  selector: 'app-export-modal',
  templateUrl: './export-modal.component.html',
  styleUrls: ['./export-modal.component.css']
})
export class ExportModalComponent implements OnInit {

    public settings:{total,from};

    constructor(public dialogRef: MatDialogRef<ExportModalComponent>,
                @Inject(MAT_DIALOG_DATA) public data: any) {
    }

    ngOnInit() {
        console.log('data into dialog', this.data.settings);
        this.settings = this.data.settings;
    }

    doExport() {
        this.dialogRef.close(this.settings);
    }
}
