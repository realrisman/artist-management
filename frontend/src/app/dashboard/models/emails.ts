export class Emails {
    id: number;
    email: string;
    status: string;
    result: string;
    notice: string;
    blocked: string;
    webmail: string;
    public offset = 0;

    public toJson() {
        return {
            id      : this.id,
            email   : this.email,
            status  : this.status,
            result  : this.result,
            notice  : this.notice,
            blocked  : this.blocked,
            webmail  : this.webmail,
        };
    }

}
