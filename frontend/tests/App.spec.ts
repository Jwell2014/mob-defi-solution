import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import App from '../src/App.vue'

describe('App.vue', () => {
  /**
   * Vérifie que le formulaire de calcul de trajet
   * s'affiche correctement avec les champs principaux
   * et le bouton d'action.
   */
  it('affiche le formulaire de calcul de trajet', () => {
    const wrapper = mount(App)

    const html = wrapper.html()

    expect(html).toContain('Station de départ')
    expect(html).toContain('Station d’arrivée')
    expect(html).toContain('Code analytique')
    expect(html).toContain('Calculer le trajet')
  })
})

describe('App.vue', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
  })

  /**
   * Vérifie que le formulaire de calcul de trajet
   * s'affiche correctement avec les champs principaux
   * et le bouton d'action.
   */
  it('affiche le formulaire de calcul de trajet', () => {
    const wrapper = mount(App)

    const html = wrapper.html()

    expect(html).toContain('Station de départ')
    expect(html).toContain('Station d’arrivée')
    expect(html).toContain('Code analytique')
    expect(html).toContain('Calculer le trajet')
  })

  /**
   * Vérifie qu'un appel API réussi affiche la route calculée.
   * On mock "fetch" pour contrôler la réponse du backend.
   */
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

    // On injecte des valeurs comme si l'utilisateur avait rempli le formulaire
    vm.fromStationId = 'MX'
    vm.toStationId = 'ZW'
    vm.analyticCode = 'ANA-123'

    // On appelle directement la méthode de soumission
    await vm.onSubmit()
    await flushPromises()

    const html = wrapper.html()

    // Vérifie que l'API a bien été appelée
    expect(fetchMock).toHaveBeenCalledTimes(1)

    // Vérifie que la réponse est affichée dans le rendu
    expect(html).toContain('MX')
    expect(html).toContain('ZW')
    expect(html).toContain('123.45') // ou '123.45 km' selon ton rendu exact
  })
})