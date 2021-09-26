import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CompanyLogComponent } from './company-log.component';

describe('CompanyLogComponent', () => {
  let component: CompanyLogComponent;
  let fixture: ComponentFixture<CompanyLogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CompanyLogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CompanyLogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
