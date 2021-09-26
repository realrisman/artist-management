import {Link} from './link';
import {Moment} from 'moment';

export class Celebrity {
    name: string               = '';
    bio: string                = '';
    profession: string         = '';
    state: string              = '';
    city: string               = '';
    source: string             = '';
    country: string            = '';
    birthdate: string | Moment = '';
    status: string             = '';
    unid: string               = '';
    id: string                 = '';
    price: string              = '';
    youtube: string            = '';
    instagram: string          = '';
    directAddress: string      = '';
    links: Link[]              = [];
    representatives: any[]     = [];
    categories: any[]          = [];
    sources                    = [];
    verification_log           = [];
    image: string              = '';
    image_title: string        = '';
    image_alt: string          = '';
    attachment_id: string      = '';
    primaryCategory: any       = null;
    deceased: boolean          = false;
    hiatus: boolean            = false;
    selfManaged: boolean       = false;
    needs_update_flag: string  = '';
    last_verify_date: string   = '';
    remove_reason: string      = '';
    unable_to_verify: boolean  = false;
    spot_checked: boolean    = false;
}
