import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CompanyLogCellRendererComponent } from './company-log-cell-renderer.component';

describe('CompanyLogCellRendererComponent', () => {
  let component: CompanyLogCellRendererComponent;
  let fixture: ComponentFixture<CompanyLogCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CompanyLogCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CompanyLogCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
