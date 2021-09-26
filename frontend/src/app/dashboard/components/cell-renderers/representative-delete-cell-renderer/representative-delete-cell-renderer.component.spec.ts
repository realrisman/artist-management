import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeDeleteCellRendererComponent } from './representative-delete-cell-renderer.component';

describe('RepresentativeDeleteCellRendererComponent', () => {
  let component: RepresentativeDeleteCellRendererComponent;
  let fixture: ComponentFixture<RepresentativeDeleteCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeDeleteCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeDeleteCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
