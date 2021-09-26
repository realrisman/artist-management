import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeEditComponent } from './representative-edit.component';

describe('RepresentativeEditComponent', () => {
  let component: RepresentativeEditComponent;
  let fixture: ComponentFixture<RepresentativeEditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeEditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeEditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
