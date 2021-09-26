import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeLogComponent } from './representative-log.component';

describe('RepresentativeLogComponent', () => {
  let component: RepresentativeLogComponent;
  let fixture: ComponentFixture<RepresentativeLogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeLogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeLogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
