import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RepresentativeLogCellRendererComponent } from './representative-log-cell-renderer.component';

describe('RepresentativeLogCellRendererComponent', () => {
  let component: RepresentativeLogCellRendererComponent;
  let fixture: ComponentFixture<RepresentativeLogCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RepresentativeLogCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RepresentativeLogCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
