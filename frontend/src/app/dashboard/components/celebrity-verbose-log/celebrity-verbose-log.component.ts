import {Component, Input, OnInit} from '@angular/core';
import {Celebrity} from '../../models/celebrity';

@Component({
    selector   : 'celebrity-verbose-log',
    templateUrl: './celebrity-verbose-log.component.html',
    styleUrls  : ['./celebrity-verbose-log.component.css']
})
export class CelebrityVerboseLogComponent implements OnInit {

    @Input()
    public old: any;
    @Input()
    public new: any;
    @Input()
    public name: string = '';

    public notModified: boolean = false;
    public newCats              = [];
    public removedCats          = [];
    public newReps              = [];
    public removedReps          = [];
    public changedReps          = [];
    public newLinks             = [];
    public removedLinks         = [];

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
        /**
         * remove links' ids cause they are new on each save
         */
        oldstripped.links = oldstripped.links.map((link) => {
            link.id = 1;
            return link;
        });
        newstripped.links = newstripped.links.map((link) => {
            link.id = 1;
            return link;
        });

        this.notModified = JSON.stringify(oldstripped) == JSON.stringify(newstripped);
        //console.log('==?', JSON.stringify(oldstripped) == JSON.stringify(newstripped));

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


        this.new.representatives.forEach((rep, i) => {
            //check if new rep exists in old version
            if (!this.old.representatives.some((old_rep) => {
                if(old_rep.is_company != rep.is_company){
                    return false;
                }
                if(rep.is_company){
                    return old_rep.company.id == rep.company.id;
                }
                return (old_rep.representative.id == rep.representative.id);
            })) {
                this.newReps.push(rep);
            }
        });

        this.old.representatives.forEach((rep, i) => {
            //check if old rep exists in new version
            if (!this.new.representatives.some((new_rep) => {
                if(new_rep.is_company != rep.is_company){
                    return false;
                }
                if(rep.is_company){
                    return new_rep.company.id == rep.company.id;
                }
                return (new_rep.representative.id == rep.representative.id);
            })) {
                this.removedReps.push(rep);
            }
        });

        this.new.links.forEach((link, i) => {
            //check if new link exists in old version
            if (!this.old.links.some((old_link) => {
                return old_link.url == link.url;
            })) {
                this.newLinks.push(link);
            }
        });

        this.old.links.forEach((link, i) => {
            //check if old link exists in new version
            if (!this.new.links.some((new_link) => {
                return new_link.url == link.url;
            })) {
                this.removedLinks.push(link);
            }
        });

        this.new.representatives.forEach((rep, i) => {
            //check if new rep exists in old version
            this.old.representatives.filter((old_rep) => {
                if(old_rep.is_company != rep.is_company){
                    return false;
                }
                if(rep.is_company){
                    return old_rep.company.id == rep.company.id;
                }
                return (old_rep.representative.id == rep.representative.id);
            }).forEach((old_rep) => {
                if (old_rep.territory != rep.territory) {
                    this.changedReps.push({
                        type : 'Territory',
                        value: rep.territory,
                        rep  : rep
                    });
                }
                if (old_rep.type != rep.type) {
                    this.changedReps.push({
                        type : 'Type',
                        value: rep.type,
                        rep  : rep
                    });
                }
            })
        });
    }

}
