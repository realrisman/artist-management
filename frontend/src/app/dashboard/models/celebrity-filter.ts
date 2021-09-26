import {Representative} from './representative';

export class CelebrityFilter {
    public agent: Representative;
    public manager: Representative;
    public publicist: Representative;
    public company: string     = '';
    public companies: string[] = [];
    public status: string      = '';
    public sort: string        = 'modified';
    public name: string        = '';
    public order: string       = 'desc';
    public offset              = 0;
    public limit               = 10;
    public noreps              = false;
    public unable_to_verify    = false;

    public toJson() {
        return {
            agent           : this.agent ? this.agent.id.toString(10) : "",
            manager         : this.manager ? this.manager.id.toString(10) : "",
            publicist       : this.publicist ? this.publicist.id.toString(10) : "",
            company         : this.company,
            "companies[]"   : this.companies,
            status          : this.status,
            sort            : this.sort,
            name            : this.name,
            noreps          : this.noreps,
            unable_to_verify: this.unable_to_verify,
            order           : this.order,
            offset          : this.offset.toString(10),
            limit           : this.limit.toString(10)
        }
    }
}
