import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import App from '../src/App.vue'

describe('App.vue', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
  })

  it('affiche le formulaire de calcul de trajet', () => {
    const wrapper = mount(App)
    const html = wrapper.html()

    expect(html).toContain('Station de départ')
    expect(html).toContain('Station d’arrivée')
    expect(html).toContain('Code analytique')
    expect(html).toContain('Calculer le trajet')
  })

  it('affiche le résultat quand l’API renvoie une route valide', async () => {
    const fakeRoute = {
      id: 'route-123',
      fromStationId: 'MX',
      toStationId: 'ZW',
      analyticCode: 'ANA-123',
      distanceKm: 123.45,
      path: ['MX', 'CGE', 'VUAR', 'ZW'],
      createdAt: '2025-11-27T16:37:01+00:00',
      debug_stationCount: 101,
      debug_neighborsFrom: { CGE: 0.65 },
    }

    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => fakeRoute,
    })
    ;(globalThis as any).fetch = fetchMock

    const wrapper = mount(App)
    const vm = wrapper.vm as any

    vm.fromStationId = 'MX'
    vm.toStationId = 'ZW'
    vm.analyticCode = 'ANA-123'

    await vm.onSubmit()
    await flushPromises()

    const html = wrapper.html()

    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(html).toContain('MX')
    expect(html).toContain('ZW')
    expect(html).toContain('123.45')
  })
})