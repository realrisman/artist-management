import {Role} from './role';

export class User {

    public static roles: Role[] = [
        new Role('ROLE_IMAGE_UPLOADER', 'Image uploader'),
        new Role('ROLE_WRITER', 'Writer'),
        new Role('ROLE_SPECTATOR', 'Spectator'),
        new Role('ROLE_SPOT_CHECKER', 'Spot Checker'),
        new Role('ROLE_EDITOR', 'Editor'),
        new Role('ROLE_TRAINER', 'Trainer'),
        new Role('ROLE_ADMIN', 'Administrator')
    ];

    private _login: string = '';
    private _role: string = '';
    private _first_name: string = '';
    private _last_name: string = '';
    private _password: string = null;
    private _password2: string = null;
    private _id: number = 0;
    private _monthly_limit: number = null;
    private _limit_used: number = null;
    private _deleted: boolean = false;


    constructor(id: number = 0, login: string, role: string, password: string = null, first_name = '', last_name = '', deleted = false, monthly_limit = null, limit_used = null) {
        this._id         = id;
        this._login      = login;
        this._role       = role;
        this._password   = password;
        this._first_name = first_name;
        this._last_name  = last_name;
        this._deleted    = deleted;
        this._monthly_limit = monthly_limit;
        this._limit_used = limit_used;
    }

    get login(): string {
        return this._login;
    }

    set login(value: string) {
        this._login = value;
    }

    get role(): string {
        return this._role;
    }

    set role(value: string) {
        this._role = value;
    }

    get password(): string {
        return this._password;
    }

    set password(value: string) {
        this._password = value;
    }


    get id(): number {
        return this._id;
    }

    set id(value: number) {
        this._id = value;
    }

    get password2(): string {
        return this._password2;
    }

    set password2(value: string) {
        this._password2 = value;
    }

    get first_name(): string {
        return this._first_name;
    }

    set first_name(value: string) {
        this._first_name = value;
    }

    get last_name(): string {
        return this._last_name;
    }

    set last_name(value: string) {
        this._last_name = value;
    }


    get deleted(): boolean {
        return this._deleted;
    }

    set deleted(value: boolean) {
        this._deleted = value;
    }


  get monthly_limit(): number {
    return this._monthly_limit;
  }

  set monthly_limit(value: number) {
    this._monthly_limit = value;
  }

  get limit_used(): number {
    return this._limit_used;
  }

  set limit_used(value: number) {
    this._limit_used = value;
  }

  public toJson() {
        return {
            login     : this.login,
            password  : this.password,
            first_name: this.first_name,
            last_name : this.last_name,
            password2 : this.password2,
            role      : this.role,
            id        : this.id,
            deleted   : this.deleted,
            monthly_limit: this.monthly_limit,
            limit_used: this.limit_used
        }
    }
}
