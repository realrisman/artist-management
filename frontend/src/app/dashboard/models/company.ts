export class Company {

    id: number;
    name: string;
    status: string;
    website: string;
    instagram: string;
    description: string;
    needs_update_flag: string = '';
    last_verify_date: string  = '';
    image: string             = '';
    image_title: string       = '';
    image_alt: string         = '';
    attachment_id: string     = '';
    source: string            = '';
    sources                   = [];
    categories: any[]         = [];
    primaryCategory: any      = null;
    created: string           = '';
    verification_log          = [];
    celebrities               = [];
    representatives           = [];

    locations: { id, name, email, phone, postal_address, visitor_address }[] = [];

    public toJson() {
        return {
            id  : this.id,
            name: this.name
        }
    }

}
