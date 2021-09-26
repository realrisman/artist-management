import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityLogComponent } from './celebrity-log.component';

describe('CelebrityLogComponent', () => {
  let component: CelebrityLogComponent;
  let fixture: ComponentFixture<CelebrityLogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityLogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityLogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
