import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LogDeleteCellRendererComponent } from './log-delete-cell-renderer.component';

describe('LogDeleteCellRendererComponent', () => {
  let component: LogDeleteCellRendererComponent;
  let fixture: ComponentFixture<LogDeleteCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LogDeleteCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LogDeleteCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
