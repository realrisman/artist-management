import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityLogCellRendererComponent } from './celebrity-log-cell-renderer.component';

describe('CelebrityLogCellRendererComponent', () => {
  let component: CelebrityLogCellRendererComponent;
  let fixture: ComponentFixture<CelebrityLogCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityLogCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityLogCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
