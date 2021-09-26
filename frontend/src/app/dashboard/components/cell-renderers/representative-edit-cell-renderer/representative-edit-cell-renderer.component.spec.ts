import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeEditCellRendererComponent } from './representative-edit-cell-renderer.component';

describe('RepresentativeEditCellRendererComponent', () => {
  let component: RepresentativeEditCellRendererComponent;
  let fixture: ComponentFixture<RepresentativeEditCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeEditCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeEditCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
