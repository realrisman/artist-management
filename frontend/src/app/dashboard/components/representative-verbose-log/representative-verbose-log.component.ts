import {Component, Input, OnInit} from '@angular/core';

@Component({
    selector   : 'representative-verbose-log',
    templateUrl: './representative-verbose-log.component.html',
    styleUrls  : ['./representative-verbose-log.component.css']
})
export class RepresentativeVerboseLogComponent implements OnInit {

    @Input()
    public old: any;
    @Input()
    public new: any;
    @Input()
    public name: string = '';

    public notModified: boolean = false;
    public newEmails            = [];
    public removedEmails        = [];
    public newPhones            = [];
    public removedPhones        = [];
    public newCats              = [];
    public removedCats          = [];

    public newCelebs     = [];
    public removedCelebs = [];
    public changedCelebs = [];

    public newCompanies     = [];
    public removedCompanies = [];

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
        // console.log('==?', JSON.stringify(oldstripped) , JSON.stringify(newstripped));

        this.new.emails.forEach((email, i) => {
            //check if new category exists in old version
            if (this.old.emails.indexOf(email) == -1) {
                this.newEmails.push(email);
            }
        });
        this.old.emails.forEach((email, i) => {
            //check if new category exists in old version
            if (this.new.emails.indexOf(email) == -1) {
                this.removedEmails.push(email);
            }
        });
        this.new.phones.forEach((phone, i) => {
            //check if new category exists in old version
            if (this.old.phones.indexOf(phone) == -1) {
                this.newPhones.push(phone);
            }
        });
        this.old.phones.forEach((phones, i) => {
            //check if new category exists in old version
            if (this.new.phones.indexOf(phones) == -1) {
                this.removedPhones.push(phones);
            }
        });

        this.new.categories.forEach((cat, i) => {
            //check if new category exists in old version
            if (!this.old.categories.some((old_cat) => {
                return old_cat.id == cat.id;
            })) {
                this.newCats.push(cat);
            }
        });

        this.old.categories.forEach((cat, i) => {
            //check if old category exists in new version
            if (!this.new.categories.some((new_cat) => {
                return new_cat.id == cat.id;
            })) {
                this.removedCats.push(cat);
            }
        });
        if(this.new.hasOwnProperty('companies')) {
            if(!this.old.hasOwnProperty('companies')){
                this.old.companies = [];
            }
            this.new.companies.forEach((cat, i) => {
                //check if new category exists in old version
                if (!this.old.companies.some((old_cat) => {
                    return old_cat.id == cat.id;
                })) {
                    this.newCompanies.push(cat);
                }
            });

            this.old.companies.forEach((cat, i) => {
                //check if old category exists in new version
                if (!this.new.companies.some((new_cat) => {
                    return new_cat.id == cat.id;
                })) {
                    this.removedCompanies.push(cat);
                }
            });
        }
        if(this.new.hasOwnProperty('celebrities')) {
            this.new.celebrities.forEach((celeb, i) => {
                //check if new rep exists in old version
                if (!this.old.celebrities.some((old_celeb) => {
                    return old_celeb.celebrity.id == celeb.celebrity.id;
                })) {
                    this.newCelebs.push(celeb);
                }
            });

            this.new.celebrities.forEach((celeb, i) => {
                //check if new rep exists in old version
                if(this.old.hasOwnProperty('celebrities')) {
                    this.old.celebrities.filter((old_celeb) => {
                        return old_celeb.celebrity.id == celeb.celebrity.id;
                    }).forEach((old_rep) => {
                        if (old_rep.territory != celeb.territory) {
                            this.changedCelebs.push({
                                type : 'Territory',
                                value: celeb.territory,
                                rep  : celeb
                            });
                        }
                        if (old_rep.type != celeb.type) {
                            this.changedCelebs.push({
                                type : 'Type',
                                value: celeb.type,
                                rep  : celeb
                            });
                        }
                    })
                }
            });

        }
        if(this.old.hasOwnProperty('celebrities')) {
            this.old.celebrities.forEach((celeb, i) => {
                //check if old rep exists in new version
                if (!this.new.celebrities.some((new_celeb) => {
                    return new_celeb.celebrity.id == celeb.celebrity.id;
                })) {
                    this.removedCelebs.push(celeb);
                }
            });
        }

    }

}
