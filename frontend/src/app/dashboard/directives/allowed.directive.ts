import {Directive, ElementRef, Input, OnInit, TemplateRef, ViewContainerRef} from '@angular/core';
import {AuthService} from '../../common/services/auth.service';

@Directive({
    selector: '[allowed]'
})
export class AllowedDirective implements OnInit {
    @Input('allowed') allowed: string;

    protected roles: string[] = [];

    constructor(private el: ElementRef, private auth: AuthService) {
    }

    ngOnInit(): void {
        this.roles = this.allowed.split(",");
        this.auth.getRole().subscribe((role) => {
            if (role && this.roles.indexOf(role.toString()) === -1) {
                this.el.nativeElement.remove();
            }
        })
    }
}

