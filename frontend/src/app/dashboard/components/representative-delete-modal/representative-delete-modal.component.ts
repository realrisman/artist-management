import {Component, Inject, OnInit} from '@angular/core';
import {AuthService} from "../../../common/services/auth.service";
import {MAT_DIALOG_DATA, MatDialogRef} from "@angular/material/dialog";

@Component({
  selector: 'app-representative-delete-modal',
  templateUrl: './representative-delete-modal.component.html',
  styleUrls: ['./representative-delete-modal.component.css']
})
export class RepresentativeDeleteModalComponent implements OnInit {

  constructor(public dialogRef: MatDialogRef<RepresentativeDeleteModalComponent>,
              @Inject(MAT_DIALOG_DATA) public data: any) {
  }

  ngOnInit() {
    console.log('data into dialog', this.data);
  }

  onSubmit($event) {
    this.dialogRef.close($event);
  }

}
