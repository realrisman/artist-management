import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CompanyVerboseLogComponent } from './company-verbose-log.component';

describe('CompanyVerboseLogComponent', () => {
  let component: CompanyVerboseLogComponent;
  let fixture: ComponentFixture<CompanyVerboseLogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CompanyVerboseLogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CompanyVerboseLogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
