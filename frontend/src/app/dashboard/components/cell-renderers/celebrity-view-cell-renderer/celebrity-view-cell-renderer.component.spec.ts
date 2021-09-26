import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityViewCellRendererComponent } from './celebrity-view-cell-renderer.component';

describe('CelebrityViewCellRendererComponent', () => {
  let component: CelebrityViewCellRendererComponent;
  let fixture: ComponentFixture<CelebrityViewCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityViewCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityViewCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
