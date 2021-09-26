import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeViewCellRendererComponent } from './representative-view-cell-renderer.component';

describe('RepresentativeViewCellRendererComponent', () => {
  let component: RepresentativeViewCellRendererComponent;
  let fixture: ComponentFixture<RepresentativeViewCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeViewCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeViewCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
