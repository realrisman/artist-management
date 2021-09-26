import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeDeleteModalComponent } from './representative-delete-modal.component';

describe('RepresentativeDeleteModalComponent', () => {
  let component: RepresentativeDeleteModalComponent;
  let fixture: ComponentFixture<RepresentativeDeleteModalComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeDeleteModalComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeDeleteModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
