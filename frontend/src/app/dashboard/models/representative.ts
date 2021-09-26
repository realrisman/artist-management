export class Representative {
    static AGENT: string     = 'agent';
    static MANAGER: string   = 'manager';
    static PUBLICIST: string = 'publicist';

    id: number;
    name: string;
    type: string;
    company: any                 = null;
    status: string;
    source: string;
    image: string                = '';
    image_title: string          = '';
    image_alt: string            = '';
    instagram: string            = '';
    mailing_address: string;
    visitor_address: string;
    companyName: string          = '';
    attachment_id: string        = '';
    phones: string[]             = [];
    emails: string[]             = [];
    categories: any[]            = [];
    companies: any[]             = [];
    location: any                = '';
    primaryCategory: any         = null;
    sources                      = [];
    verification_log             = [];
    celebrities                  = [];
    needs_update_flag: string    = '';
    last_verify_date: string     = '';
    remove_reason: string        = '';
    allows_to_add_phone: boolean = true;
    unable_to_verify: boolean    = false;
    spot_checked: boolean    = false;

    public toJson() {
        return {
            id     : this.id,
            name   : this.name,
            type   : this.type,
            company: this.company
        }
    }

}
