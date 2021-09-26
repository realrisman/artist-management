import { TestBed, inject } from '@angular/core/testing';

import { RepresentativesService } from './representatives.service';

describe('RepresentativesService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [RepresentativesService]
    });
  });

  it('should be created', inject([RepresentativesService], (service: RepresentativesService) => {
    expect(service).toBeTruthy();
  }));
});
