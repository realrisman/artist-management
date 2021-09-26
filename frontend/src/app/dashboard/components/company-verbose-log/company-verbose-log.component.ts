import {Component, Input, OnInit} from '@angular/core';

@Component({
  selector: 'company-verbose-log',
  templateUrl: './company-verbose-log.component.html',
  styleUrls: ['./company-verbose-log.component.css']
})
export class CompanyVerboseLogComponent implements OnInit {

    @Input()
    public old: any;
    @Input()
    public new: any;
    @Input()
    public name: string = '';

    public notModified: boolean = false;
    public newLocations         = [];
    public removedLocations     = [];
    public changedLocations = [];

    constructor() {
    }

    ngOnInit() {
        let oldstripped = JSON.parse(JSON.stringify(this.old));
        let newstripped = JSON.parse(JSON.stringify(this.new));

        oldstripped['valid_from'] = "";
        newstripped['valid_from'] = "";
        newstripped['user']       = "";
        oldstripped['user']       = "";
        newstripped['source']     = "";
        oldstripped['source']     = "";


        this.notModified = JSON.stringify(oldstripped) == JSON.stringify(newstripped);
        //console.log('==?', JSON.stringify(oldstripped) == JSON.stringify(newstripped));


        this.new.locations.forEach((location, i) => {
            //check if new rep exists in old version
            if (!this.old.locations.some((old_location) => {
                return old_location.id == location.id;
            })) {
                this.newLocations.push(location);
            }
        });

        this.old.locations.forEach((location, i) => {
            //check if old rep exists in new version
            if (!this.new.locations.some((new_location) => {
                return new_location.id == location.id;
            })) {
                this.removedLocations.push(location);
            }
        });

        this.new.locations.forEach((location, i) => {
            //check if new rep exists in old version
            this.old.locations.filter((old_location) => {
                return old_location.id == location.id;
            }).forEach((old_location) => {
                if (old_location.name != location.name) {
                    this.changedLocations.push({
                        type : 'Name',
                        value: location.name,
                        location  : location
                    });
                }
                if (old_location.visitor_address != location.visitor_address) {
                    this.changedLocations.push({
                        type : 'Visitor address',
                        value: location.visitor_address,
                        location  : location
                    });
                }
                if (old_location.postal_address != location.postal_address) {
                    this.changedLocations.push({
                        type : 'Postal address',
                        value: location.postal_address,
                        location  : location
                    });
                }
                if (old_location.email != location.email) {
                    this.changedLocations.push({
                        type : 'E-mail',
                        value: location.email,
                        location  : location
                    });
                }
                if (old_location.phone != location.phone) {
                    this.changedLocations.push({
                        type : 'Phone',
                        value: location.phone,
                        location  : location
                    });
                }
            })
        });
    }
}
