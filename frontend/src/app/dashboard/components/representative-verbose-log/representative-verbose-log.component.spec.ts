import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeVerboseLogComponent } from './representative-verbose-log.component';

describe('RepresentativeVerboseLogComponent', () => {
  let component: RepresentativeVerboseLogComponent;
  let fixture: ComponentFixture<RepresentativeVerboseLogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeVerboseLogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeVerboseLogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
