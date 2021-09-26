import {Directive, Input, OnInit} from '@angular/core';
import {AbstractControl, FormControl, FormGroup, NG_VALIDATORS} from '@angular/forms';

@Directive({
    selector : '[RepeatPassword]',
    providers: [{provide: NG_VALIDATORS, useExisting: RepeatPasswordDirective, multi: true}]
})
/**
 * validates fields fo equal values
 */
export class RepeatPasswordDirective {

    /**
     * field names delimited by comma
     */
    @Input('RepeatPassword') repeatPasswordFields: string;

    protected control: AbstractControl;
    protected control2: AbstractControl;

    validate(control: AbstractControl): { [key: string]: any } {
        if (!this.repeatPasswordFields) {
            return null;
        }

        let name, name2;
        [name, name2] = this.repeatPasswordFields.split(",");

        if (this.control == null && name != "") {
            this.control = control.get(name);
        }

        if (this.control2 == null && name2 != "") {
            this.control2 = control.get(name2);
        }

        /**
         * if controls are not ready yet validation is passing
         */
        if (!this.control2 || !this.control) {
            return null;
        }

        if ((this.control.value || this.control2.value) && (this.control.value != this.control2.value)) {
            return {
                'RepeatPassword': true
            }
        }
        return null;
    }
}
